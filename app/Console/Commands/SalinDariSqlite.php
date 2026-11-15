<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menyalin seluruh isi database SQLite lama ke koneksi database default (PostgreSQL).
 * Struktur tabel tujuan harus sudah dibuat lewat `php artisan migrate`.
 */
class SalinDariSqlite extends Command
{
    protected $signature = 'db:salin-dari-sqlite
        {--path= : Path file SQLite sumber (default: database/database.sqlite)}
        {--force : Tetap jalankan walau tabel users di tujuan sudah berisi data}';

    protected $description = 'Salin data dari database SQLite lama ke database default (PostgreSQL)';

    /** Tabel yang tidak perlu disalin karena dibuat ulang oleh migrate/aplikasi. */
    private const LEWATI = ['migrations', 'cache', 'cache_locks'];

    public function handle(): int
    {
        $path = $this->option('path') ?: database_path('database.sqlite');

        if (! is_file($path)) {
            $this->error("File SQLite tidak ditemukan: {$path}");

            return self::FAILURE;
        }

        if (DB::connection()->getDriverName() !== 'pgsql') {
            $this->error('Koneksi database default harus pgsql (cek DB_CONNECTION).');

            return self::FAILURE;
        }

        config(['database.connections.sqlite_sumber' => [
            'driver' => 'sqlite',
            'database' => $path,
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]]);

        $sumber = DB::connection('sqlite_sumber');
        $tujuan = DB::connection();

        if (! $this->option('force') && $tujuan->table('users')->exists()) {
            $this->error('Tabel users di tujuan sudah berisi data. Pakai --force untuk mengosongkan lalu menyalin ulang.');

            return self::FAILURE;
        }

        if ($this->option('force') && ! $this->confirm('Semua data di database tujuan akan dihapus lalu diganti isi SQLite. Lanjutkan?')) {
            return self::FAILURE;
        }

        $tabel = $this->urutkanTabel($sumber);

        $tujuan->transaction(function () use ($sumber, $tujuan, $tabel) {
            // Kosongkan tujuan (urutan terbalik agar foreign key aman)
            foreach (array_reverse($tabel) as $nama) {
                $tujuan->table($nama)->delete();
            }

            foreach ($tabel as $nama) {
                $tipeBoolean = collect(Schema::getColumns($nama))
                    ->filter(fn ($kolom) => $kolom['type_name'] === 'bool')
                    ->pluck('name')
                    ->all();

                $kolomTujuan = Schema::getColumnListing($nama);
                $punyaId = in_array('id', $kolomTujuan, true);
                $jumlah = 0;

                $query = $sumber->table($nama);
                $punyaId ? $query->orderBy('id') : $query->orderByRaw('rowid');

                foreach ($query->cursor()->chunk(500) as $baris) {
                    $data = $baris->map(function ($row) use ($tipeBoolean, $kolomTujuan) {
                        $row = array_intersect_key((array) $row, array_flip($kolomTujuan));
                        foreach ($tipeBoolean as $kolom) {
                            if (isset($row[$kolom])) {
                                $row[$kolom] = (bool) $row[$kolom];
                            }
                        }

                        return $row;
                    })->all();

                    $tujuan->table($nama)->insert($data);
                    $jumlah += count($data);
                }

                // Lanjutkan auto-increment dari ID terbesar (hanya kolom id yang punya sequence)
                $sequence = $punyaId
                    ? $tujuan->selectOne("SELECT pg_get_serial_sequence('\"{$nama}\"', 'id') AS seq")->seq
                    : null;

                if ($sequence) {
                    $tujuan->statement(
                        "SELECT setval(?, COALESCE(MAX(id), 1), MAX(id) IS NOT NULL) FROM \"{$nama}\"",
                        [$sequence]
                    );
                }

                $this->line(sprintf('  %-22s %d baris', $nama, $jumlah));
            }
        });

        $this->info('Data berhasil disalin dari SQLite.');

        return self::SUCCESS;
    }

    /**
     * Ambil tabel SQLite yang juga ada di tujuan, diurutkan agar tabel induk
     * (yang dirujuk foreign key) disalin lebih dulu.
     *
     * @return list<string>
     */
    private function urutkanTabel(ConnectionInterface $sumber): array
    {
        $semua = collect($sumber->select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'"))
            ->pluck('name')
            ->reject(fn ($nama) => in_array($nama, self::LEWATI, true) || ! Schema::hasTable($nama))
            ->values()
            ->all();

        $induk = [];
        foreach ($semua as $nama) {
            $induk[$nama] = collect($sumber->select("PRAGMA foreign_key_list(\"{$nama}\")"))
                ->pluck('table')
                ->reject(fn ($t) => $t === $nama || ! in_array($t, $semua, true))
                ->unique()
                ->all();
        }

        $urutan = [];
        while (count($urutan) < count($semua)) {
            $maju = false;
            foreach ($semua as $nama) {
                if (! in_array($nama, $urutan, true) && array_diff($induk[$nama], $urutan) === []) {
                    $urutan[] = $nama;
                    $maju = true;
                }
            }
            if (! $maju) {
                // Siklus foreign key: tambahkan sisanya apa adanya
                $urutan = array_values(array_unique(array_merge($urutan, $semua)));
            }
        }

        return $urutan;
    }
}
