@if (session('status'))
    <div class="toast" role="status" data-toast>
        <i class="fa-solid fa-circle-check"></i>
        <span>{{ session('status') }}</span>
        <button type="button" aria-label="Tutup" data-toast-close><i class="fa-solid fa-xmark"></i></button>
    </div>
@endif
