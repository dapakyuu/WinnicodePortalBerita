# Tutorial Deploy Portal Berita WinniCode ke VPS Hostinger (Docker Manager)

Target akhir: website bisa dibuka di **https://winnicode.forensa.tech**, dan setiap
kali kamu `git push` ke branch `main`, VPS otomatis ikut ter-update.
**Semua langkah dilakukan lewat hPanel dan GitHub, tanpa terminal VPS.**

```
 Kamu ──git push──▶ GitHub Actions ──Hostinger API──▶ Docker Manager di VPS
                                                        │  (build dari GitHub)
                     Pengunjung ──HTTPS──▶ Traefik (sudah ada di VPS)
                                                        ├──▶ forensa.tech           → Forensa
                                                        ├──▶ daffa.forensa.tech     → Portofolio
                                                        └──▶ winnicode.forensa.tech → Portal Berita
```

Cara kerjanya sama dengan Portofolio:

- GitHub Actions mengirim `docker-compose.yml` ke **Hostinger API** memakai API key.
- Docker Manager mengambil source code langsung dari GitHub pada commit terbaru,
  lalu mem-build dan menjalankannya sebagai project **winnicode**.
- **Traefik** yang sudah berjalan di VPS meneruskan `winnicode.forensa.tech` ke
  container dan membuat sertifikat HTTPS secara otomatis.

Bedanya dengan Portofolio, website ini punya **data yang harus disimpan**: database
PostgreSQL (service `db`, volume `winnicode-pgdata`) dan gambar unggahan (volume
`winnicode-storage`). Keduanya **tidak hilang** saat redeploy.

> **Pindah dari SQLite:** saat container start dan database PostgreSQL masih kosong,
> `entrypoint.sh` otomatis menyalin isi file SQLite lama
> (`storage/database/database.sqlite` di volume `winnicode-storage`) lewat
> `php artisan db:salin-dari-sqlite`. Jika file itu tidak ada, seeder data awal dijalankan.
> File SQLite lama tidak dihapus, jadi tetap bisa dipakai sebagai cadangan.

Isi container (lihat `Dockerfile` dan `docker/`):

| Bagian           | Fungsi                                                               |
|------------------|----------------------------------------------------------------------|
| Nginx            | Menyajikan file statis & meneruskan request PHP                      |
| PHP 8.3 FPM      | Menjalankan Laravel                                                  |
| `entrypoint.sh`  | Setiap start: cache config/route/view, `migrate`, dan isi data awal jika database masih kosong |

---

## Daftar isi

1. [Buat subdomain `winnicode.forensa.tech`](#1-buat-subdomain-winnicodeforensatech)
2. [Siapkan API key, VM ID, APP_KEY, dan password admin](#2-siapkan-api-key-vm-id-app_key-dan-password-admin)
3. [Simpan ke GitHub](#3-simpan-ke-github)
4. [Jalankan deploy pertama](#4-jalankan-deploy-pertama)
5. [Cek hasilnya](#5-cek-hasilnya)
6. [Pemakaian sehari-hari](#6-pemakaian-sehari-hari)
7. [Backup data](#7-backup-data)
8. [Troubleshooting](#8-troubleshooting)

---

## 1. Buat subdomain `winnicode.forensa.tech`

1. Login ke [hPanel Hostinger](https://hpanel.hostinger.com).
2. Buka **Domains** → pilih **forensa.tech** → **DNS / Nameservers**.
3. Di bagian **Manage DNS records**, tambahkan record:

   | Type | Name        | Points to       | TTL   |
   |------|-------------|-----------------|-------|
   | `A`  | `winnicode` | `187.52.122.64` | `300` |

   > Isi **Name** hanya `winnicode`. Hostinger otomatis menambahkan `.forensa.tech`.
   > IP di atas sama dengan record `daffa` milik Portofolio; pastikan masih IP VPS kamu.

4. Klik **Add Record**.

**Cek DNS sudah aktif** (biasanya 5–30 menit):

```bash
nslookup winnicode.forensa.tech 8.8.8.8
```

Sudah benar jika hasilnya menampilkan IP VPS.

---

## 2. Siapkan API key, VM ID, APP_KEY, dan password admin

### API key & VM ID Hostinger

Sama persis dengan Portofolio:

- **API key**: hPanel → **Profile → API** → **Generate token**. Boleh memakai token
  yang sama dengan Portofolio jika kamu masih menyimpan salinannya.
- **VM ID**: angka di URL hPanel VPS, mis. `https://hpanel.hostinger.com/vps/123456/overview` → `123456`.

### APP_KEY (kunci enkripsi Laravel)

Jalankan di folder project:

```bash
php artisan key:generate --show
```

Salin hasilnya, contoh: `base64:AbCdEf...=`. **Jangan pernah mengganti APP_KEY**
setelah website live; semua sesi login akan ter-logout.

### ADMIN_PASSWORD

Password untuk akun admin **`daffa`** yang dibuat saat deploy pertama. Pakai password
yang kuat (minimal 12 karakter). Password ini hanya dipakai **sekali**, saat database
masih kosong. Setelah itu ganti password lewat **Edit Profile** atau dashboard admin.

> Di production, akun penulis contoh lain (louis, james, dst.) diberi password acak,
> jadi tidak bisa dipakai login. User baru mendaftar sendiri lewat halaman Register.

### DB_PASSWORD

Password database PostgreSQL (user `winnicode`). Buat acak dan panjang, mis.
`openssl rand -base64 24`. Password ini dipakai saat volume `winnicode-pgdata` pertama
kali dibuat, jadi **jangan diganti** setelah deploy pertama. Kalau diganti, container
aplikasi tidak bisa login ke database.

---

## 3. Simpan ke GitHub

Buka https://github.com/dapakyuu/WinnicodePortalBerita/settings/secrets/actions
(**Settings → Secrets and variables → Actions**).

**Tab "Secrets"** → **New repository secret**:

| Name                | Value                              |
|---------------------|------------------------------------|
| `HOSTINGER_API_KEY` | API key dari langkah 2             |
| `APP_KEY`           | hasil `php artisan key:generate --show` |
| `ADMIN_PASSWORD`    | password admin pilihanmu           |
| `DB_PASSWORD`       | password database PostgreSQL       |
| `GOOGLE_CLIENT_ID`     | (opsional) OAuth Client ID untuk login Google |
| `GOOGLE_CLIENT_SECRET` | (opsional) OAuth Client Secret untuk login Google |

> **Login dengan Google:** buat OAuth Client di
> [Google Cloud Console → Credentials](https://console.cloud.google.com/apis/credentials)
> (tipe *Web application*), isi **Authorized redirect URI** dengan
> `https://winnicode.forensa.tech/auth/google/callback`, lalu simpan Client ID & Secret
> sebagai dua secret di atas. Selama secret ini kosong, tombol Google tidak ditampilkan.

**Tab "Variables"** → **New repository variable**:

| Name              | Value                               |
|-------------------|-------------------------------------|
| `HOSTINGER_VM_ID` | VM ID dari langkah 2, mis. `123456` |

---

## 4. Jalankan deploy pertama

1. Buka https://github.com/dapakyuu/WinnicodePortalBerita/actions.
2. Pilih workflow **Build & Deploy ke VPS** → **Run workflow** → **Run workflow**.

| Job                          | Fungsi                                                              |
|------------------------------|---------------------------------------------------------------------|
| **Build & uji image Docker** | Mem-build image, menjalankannya, dan mengecek beranda, daftar berita, file statis, serta proteksi `/admin`. Kalau gagal, deploy dibatalkan. |
| **Deploy ke VPS Hostinger**  | Mengirim perintah deploy ke Hostinger API.                          |

Tunggu sampai keduanya bercentang hijau ✅ (±3–8 menit).

> Peringatan *"Belum diisi: ... Deploy dilewati"* berarti ada secret/variable di
> langkah 3 yang belum tersimpan atau namanya salah ketik.

---

## 5. Cek hasilnya

1. **hPanel → VPS → Docker Manager**: project **winnicode** harus **Running**.
   Klik project → **Logs**. Pada start pertama akan muncul
   `Database kosong: menjalankan seeder data awal...` lalu `WinniCode siap di port 80`.
2. Buka **https://winnicode.forensa.tech**. Saat pertama kali dibuka, Traefik butuh
   beberapa detik untuk membuat sertifikat HTTPS.
3. Login di **https://winnicode.forensa.tech/login** dengan username `daffa` dan
   `ADMIN_PASSWORD`, lalu buka **https://winnicode.forensa.tech/admin**.

> Beranda pertama kali dibuka bisa memakan ±10 detik karena mengambil data klasemen,
> cuaca, dan berita ANTARA. Setelah itu data di-cache 30 menit.

---

## 6. Pemakaian sehari-hari

```bash
git add .
git commit -m "Perubahan baru"
git push
```

GitHub Actions otomatis build, uji, lalu deploy. Data (berita, user, komentar,
gambar unggahan) **tetap ada** karena tersimpan di volume. Migration baru ikut
dijalankan otomatis saat container start.

> Jangan mengedit compose project ini langsung di Docker Manager. Setiap deploy dari
> GitHub akan menimpanya. Ubah `docker-compose.yml` di repo lalu push.

### Rollback

```bash
git revert HEAD
git push
```

Hati-hati jika commit yang di-revert berisi migration: kolom/tabel yang sudah dibuat
di database tidak otomatis dihapus.

---

## 7. Backup data

Data tersimpan di dua volume:

- `winnicode-pgdata` — seluruh isi database PostgreSQL
- `winnicode-storage` → `app/public/` — gambar unggahan (berita, avatar, banner, kategori),
  plus `database/database.sqlite` (database lama sebelum pindah ke PostgreSQL)

Jangan menghapus volume ini (mis. tombol **Delete** project beserta volume di Docker
Manager), karena seluruh data website ikut hilang. Untuk backup, lewat terminal VPS:

```bash
# Database PostgreSQL
docker exec winnicode-db-1 pg_dump -U winnicode -d winnicode -Fc > winnicode-$(date +%F).dump

# Gambar unggahan
docker run --rm -v winnicode_winnicode-storage:/data -v "$PWD":/backup alpine \
  tar czf /backup/winnicode-storage-$(date +%F).tar.gz -C /data .
```

Pulihkan database dengan
`docker exec -i winnicode-db-1 pg_restore -U winnicode -d winnicode --clean < file.dump`.

(Nama container/volume bisa dicek dengan `docker ps | grep winnicode` dan
`docker volume ls | grep winnicode`.)

---

## 8. Troubleshooting

### Job "Build & uji image Docker" gagal

Buka log job di tab Actions. Langkah **Tampilkan log container** memperlihatkan error
Laravel. Selama build gagal, VPS tetap menjalankan versi lama.

### Deploy gagal `401` / `404`

`401`: API key salah → buat token baru dan perbarui `HOSTINGER_API_KEY`.
`404`: `HOSTINGER_VM_ID` salah atau tersimpan di tab Secrets (harus di tab **Variables**).

### Container terus restart (status Restarting / Exited)

Buka **Docker Manager → winnicode → Logs**.

- `APP_KEY belum diisi` → secret `APP_KEY` kosong.
- `ADMIN_PASSWORD wajib diisi` → secret `ADMIN_PASSWORD` kosong saat deploy pertama.
- Error `git` saat build → repo harus **public**, atau tambahkan deploy key
  ([panduan Hostinger](https://www.hostinger.com/support/how-to-deploy-from-private-github-repository-on-hostinger-docker-manager/)).

### `404 page not found` (teks polos) atau `Bad Gateway`

Sama seperti Portofolio. `404` polos berarti Traefik belum mengenali container
(cek status Running & domain di `docker-compose.yml`). `Bad Gateway` berarti beda
jaringan Docker. Tambahkan jaringan Traefik di `docker-compose.yml`:

```yaml
services:
  winnicode:
    labels:
      # ... label yang sudah ada, tambahkan:
      - "traefik.docker.network=traefik-proxy"
    networks:
      - traefik-proxy

networks:
  traefik-proxy:
    external: true
```

### Halaman tampil tanpa CSS / gambar tidak muncul / form error 419

Biasanya karena akses lewat `http://`. Pastikan membuka `https://winnicode.forensa.tech`.
Laravel sudah dikonfigurasi mempercayai header HTTPS dari Traefik (`trustProxies`).

### Gambar unggahan hilang setelah deploy

Pastikan bagian `volumes:` di `docker-compose.yml` tidak diubah dan volume
`winnicode-storage` tidak dihapus.

---

## Lampiran: menjalankan image Docker di komputer sendiri

```bash
docker build -t winnicode .
docker network create winnicode-local
docker run -d --name winnicode-db --network winnicode-local \
  -e POSTGRES_DB=winnicode -e POSTGRES_USER=winnicode -e POSTGRES_PASSWORD=rahasia \
  -v winnicode-local-pg:/var/lib/postgresql postgres:18-alpine
docker run --rm -p 8080:80 --network winnicode-local \
  -e APP_KEY="$(php artisan key:generate --show)" \
  -e APP_URL=http://localhost:8080 -e ADMIN_PASSWORD=password123 \
  -e DB_CONNECTION=pgsql -e DB_HOST=winnicode-db -e DB_DATABASE=winnicode \
  -e DB_USERNAME=winnicode -e DB_PASSWORD=rahasia \
  -v winnicode-local:/var/www/html/storage \
  winnicode
# buka http://localhost:8080
```
