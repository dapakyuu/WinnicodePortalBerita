{{-- Tombol hapus dengan modal konfirmasi. Param: $action, $message, $buttonLabel (opsional), $class (opsional) --}}
<form action="{{ $action }}" method="POST" class="d-inline" data-confirm="{{ $message }}">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn {{ $class ?? 'btn-sm btn-outline-danger' }}" title="Hapus">
        <i class="fas fa-trash-can"></i>{{ isset($buttonLabel) ? " {$buttonLabel}" : "" }}
    </button>
</form>
