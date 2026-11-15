@if (\App\Http\Controllers\GoogleAuthController::enabled())
    <div class="auth__divider"><span>atau</span></div>
    <a href="{{ route('auth.google') }}" class="btn-soft auth__google">
        <i class="fa-brands fa-google" aria-hidden="true"></i>
        Lanjutkan dengan Google
    </a>
@endif
