<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirect;
use Throwable;

/** Login / daftar memakai akun Google (Laravel Socialite). */
class GoogleAuthController extends Controller
{
    public function redirect(): SymfonyRedirect
    {
        abort_unless(self::enabled(), 404);

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        abort_unless(self::enabled(), 404);

        try {
            $google = Socialite::driver('google')->user();
        } catch (InvalidStateException) {
            // Sesi kedaluwarsa / tombol back: bukan error aplikasi
            return $this->gagal('Sesi login Google kedaluwarsa. Coba lagi.');
        } catch (Throwable $e) {
            report($e);

            return $this->gagal('Login dengan Google gagal atau dibatalkan. Coba lagi.');
        }

        $email = Str::lower((string) $google->getEmail());

        if ($email === '' || ! ($google->user['email_verified'] ?? false)) {
            return $this->gagal('Email akun Google kamu belum terverifikasi.');
        }

        $user = User::where('google_id', $google->getId())->first();

        if (! $user && $user = User::whereRaw('lower(email) = ?', [$email])->first()) {
            if ($user->google_id !== null) {
                return $this->gagal('Email ini sudah terhubung dengan akun Google lain.');
            }
            if ($user->isAdmin()) {
                return $this->gagal('Akun admin harus login memakai username/email dan password.');
            }

            $this->tautkan($user, $google->getId());
        } elseif (! $user) {
            if (Str::length($email) > 50) {
                return $this->gagal('Email Google terlalu panjang (maksimal 50 karakter). Silakan daftar manual.');
            }

            $user = new User([
                'name' => Str::limit($google->getName() ?: Str::before($email, '@'), 50, ''),
                'username' => $this->usernameUnik($email),
                'email' => $email,
                'avatar' => $google->getAvatar(),
            ]);
            $user->forceFill([
                'google_id' => $google->getId(),
                'email_verified_at' => now(),
            ])->save();
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(route('home'))
            ->with('status', 'Selamat datang, '.$user->name.'!');
    }

    /** Tombol & route Google hanya aktif bila kredensial OAuth sudah diisi. */
    public static function enabled(): bool
    {
        return filled(config('services.google.client_id')) && filled(config('services.google.client_secret'));
    }

    /**
     * Hubungkan akun lama ke Google. Email akun lama belum pernah diverifikasi, jadi
     * siapa pun bisa saja mendaftarkannya lebih dulu. Google sudah membuktikan pemilik
     * email yang asli, jadi password lama dihapus dan semua sesi lain diputus agar
     * pendaftar palsu kehilangan akses. Pemilik bisa membuat password baru di profil.
     */
    private function tautkan(User $user, string $googleId): void
    {
        $belumTerverifikasi = $user->email_verified_at === null;

        $user->forceFill([
            'google_id' => $googleId,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ]);

        if ($belumTerverifikasi) {
            $user->forceFill(['password' => null, 'remember_token' => Str::random(60)]);
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        $user->save();
    }

    private function gagal(string $pesan): RedirectResponse
    {
        return redirect()->route('login')->withErrors(['login' => $pesan]);
    }

    private function usernameUnik(string $email): string
    {
        $dasar = Str::limit(Str::slug(Str::before($email, '@'), '_') ?: 'user', 40, '');
        $username = $dasar;

        while (User::where('username', $username)->exists()) {
            $username = $dasar.'_'.Str::lower(Str::random(5));
        }

        return $username;
    }
}
