<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.google.client_id' => 'test-id',
            'services.google.client_secret' => 'test-secret',
        ]);
    }

    private function fakeGoogle(array $data): void
    {
        $user = (new GoogleUser)->map([
            'id' => $data['id'] ?? 'google-123',
            'name' => $data['name'] ?? 'Budi Santoso',
            'email' => $data['email'] ?? 'budi@gmail.com',
            'avatar' => 'https://lh3.googleusercontent.com/a/foto',
        ])->setRaw(['email_verified' => $data['verified'] ?? true]);

        $provider = Mockery::mock(GoogleProvider::class);
        $provider->shouldReceive('user')->andReturn($user);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_route_tidak_tersedia_bila_kredensial_kosong(): void
    {
        config(['services.google.client_id' => null]);

        $this->get('/auth/google')->assertNotFound();
        $this->get('/login')->assertOk()->assertDontSee('Lanjutkan dengan Google');
    }

    public function test_tombol_google_tampil_di_login_dan_register(): void
    {
        $this->get('/login')->assertOk()->assertSee('Lanjutkan dengan Google');
        $this->get('/register')->assertOk()->assertSee('Lanjutkan dengan Google');
    }

    public function test_redirect_ke_google(): void
    {
        $this->get('/auth/google')->assertRedirectContains('accounts.google.com');
    }

    public function test_user_baru_dibuat_dan_login(): void
    {
        $this->fakeGoogle([]);

        $this->get('/auth/google/callback')->assertRedirect(route('home'));

        $user = User::where('email', 'budi@gmail.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('google-123', $user->google_id);
        $this->assertSame('budi', $user->username);
        $this->assertNull($user->password);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_akun_lama_dengan_email_sama_dihubungkan(): void
    {
        $lama = User::factory()->create(['email' => 'budi@gmail.com', 'username' => 'budi']);
        $this->fakeGoogle([]);

        $this->get('/auth/google/callback')->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($lama);
        $this->assertSame('google-123', $lama->fresh()->google_id);
        $this->assertSame(1, User::count());
    }

    public function test_username_bentrok_diberi_akhiran(): void
    {
        User::factory()->create(['username' => 'budi', 'email' => 'lain@example.com']);
        $this->fakeGoogle([]);

        $this->get('/auth/google/callback');

        $this->assertStringStartsWith('budi_', User::where('email', 'budi@gmail.com')->value('username'));
    }

    public function test_email_belum_terverifikasi_ditolak(): void
    {
        $this->fakeGoogle(['verified' => false]);

        $this->get('/auth/google/callback')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('login');
        $this->assertGuest();
        $this->assertSame(0, User::count());
    }

    public function test_state_kedaluwarsa_kembali_ke_login(): void
    {
        $provider = Mockery::mock(GoogleProvider::class);
        $provider->shouldReceive('user')->andThrow(new InvalidStateException);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get('/auth/google/callback')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_user_google_tidak_bisa_login_password_dan_bisa_buat_password(): void
    {
        $user = User::factory()->create(['email' => 'budi@gmail.com', 'username' => 'budi', 'password' => null]);

        $this->post('/login', ['login' => 'budi', 'password' => ''])->assertSessionHasErrors();
        $this->post('/login', ['login' => 'budi', 'password' => 'apapun123'])->assertSessionHasErrors('login');
        $this->assertGuest();

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'username' => 'budi',
            'email' => 'budi@gmail.com',
            'password' => 'PasswordBaru123',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(password_verify('PasswordBaru123', $user->fresh()->password));
    }

    public function test_akun_belum_terverifikasi_dihapus_password_dan_sesinya(): void
    {
        // Penyerang mendaftar lebih dulu memakai email korban
        $palsu = User::factory()->create([
            'email' => 'Budi@Gmail.com', 'username' => 'budi', 'email_verified_at' => null,
        ]);
        DB::table('sessions')->insert([
            'id' => 'sesi-penyerang', 'user_id' => $palsu->id, 'payload' => '', 'last_activity' => time(),
        ]);
        $this->fakeGoogle([]);

        $this->get('/auth/google/callback')->assertRedirect(route('home'));

        $user = $palsu->fresh();
        $this->assertAuthenticatedAs($user);
        $this->assertNull($user->password);
        $this->assertNotNull($user->email_verified_at);
        $this->assertDatabaseMissing('sessions', ['id' => 'sesi-penyerang']);
    }

    public function test_akun_admin_tidak_ditautkan_otomatis(): void
    {
        $admin = User::factory()->create(['email' => 'budi@gmail.com']);
        $admin->forceFill(['role' => 'admin'])->save();
        $this->fakeGoogle([]);

        $this->get('/auth/google/callback')->assertRedirect(route('login'))->assertSessionHasErrors('login');

        $this->assertGuest();
        $this->assertNull($admin->fresh()->google_id);
    }

    public function test_email_yang_sudah_tertaut_google_lain_ditolak(): void
    {
        $user = User::factory()->create(['email' => 'budi@gmail.com']);
        $user->forceFill(['google_id' => 'google-lain'])->save();
        $this->fakeGoogle([]);

        $this->get('/auth/google/callback')->assertRedirect(route('login'))->assertSessionHasErrors('login');

        $this->assertGuest();
        $this->assertSame('google-lain', $user->fresh()->google_id);
    }
}
