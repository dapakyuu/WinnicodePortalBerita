@foreach (['status' => ['success', 'fa-circle-check'], 'error' => ['danger', 'fa-circle-exclamation']] as $key => [$type, $icon])
    @if (session($key))
        <div class="alert alert-{{ $type }} alert-dismissible fade show" role="alert">
            <i class="fas {{ $icon }} mr-2"></i>{{ session($key) }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif
@endforeach

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-circle-exclamation mr-2"></i><strong>Periksa kembali isian:</strong>
        <ul class="mb-0 mt-1 pl-4">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
    </div>
@endif
