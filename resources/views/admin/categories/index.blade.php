@extends('admin.layouts.app')

@section('title', 'Kategori')
@section('breadcrumb')<li class="breadcrumb-item active">Kategori</li>@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <form method="GET" class="form-inline">
                <div class="input-group input-group-sm">
                    <input type="search" name="q" class="form-control" placeholder="Cari kategori..." value="{{ request('q') }}" aria-label="Cari kategori">
                    <div class="input-group-append"><button class="btn btn-default" type="submit" aria-label="Cari"><i class="fas fa-search"></i></button></div>
                </div>
            </form>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-sm btn-danger ml-auto"><i class="fas fa-plus"></i> Tambah Kategori</a>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-valign-middle mb-0">
                <thead>
                    <tr><th style="width: 90px">Gambar</th><th>Nama</th><th>Slug</th><th class="text-right">Jumlah berita</th><th class="text-right" style="width: 160px">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td><img src="{{ $category->image_url }}" alt="" class="table-thumb"></td>
                            <td class="font-weight-bold">{{ $category->name }}</td>
                            <td><code>{{ $category->slug }}</code></td>
                            <td class="text-right">
                                <a href="{{ route('admin.posts.index', ['category' => $category->id]) }}">{{ $category->posts_count }}</a>
                            </td>
                            <td class="text-right text-nowrap">
                                <a href="{{ route('news.category', $category) }}" class="btn btn-sm btn-outline-secondary" target="_blank" title="Lihat"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-pen"></i></a>
                                @if ($category->posts_count)
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus" data-toggle="modal" data-target="#moveModal"
                                        data-action="{{ route('admin.categories.destroy', $category) }}" data-name="{{ $category->name }}" data-id="{{ $category->id }}" data-count="{{ $category->posts_count }}">
                                        <i class="fas fa-trash-can"></i>
                                    </button>
                                @else
                                    @include('admin.partials.delete-button', ['action' => route('admin.categories.destroy', $category), 'message' => "Hapus kategori \"{$category->name}\"?"])
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-5">Belum ada kategori.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($categories->hasPages())
            <div class="card-footer clearfix">{{ $categories->links('pagination::bootstrap-4') }}</div>
        @endif
    </div>

    {{-- Kategori berisi berita: pindahkan beritanya dulu sebelum dihapus --}}
    <div class="modal fade" id="moveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" class="modal-content" data-move-form>
                @csrf @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Hapus kategori <span data-move-name></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p>Kategori ini masih memiliki <strong data-move-count></strong> berita. Pindahkan beritanya ke:</p>
                    <select name="move_to" class="form-control" required aria-label="Kategori tujuan">
                        <option value="">Pilih kategori tujuan</option>
                        @foreach ($allCategories as $option)
                            <option value="{{ $option->id }}">{{ $option->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Pindahkan & hapus</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('#moveModal').on('show.bs.modal', function (event) {
            const btn = $(event.relatedTarget);
            const modal = $(this);
            modal.find('[data-move-form]').attr('action', btn.data('action'));
            modal.find('[data-move-name]').text('"' + btn.data('name') + '"');
            modal.find('[data-move-count]').text(btn.data('count'));
            modal.find('select option').prop('disabled', false).filter('[value="' + btn.data('id') + '"]').prop('disabled', true);
            modal.find('select').val('');
        });
    </script>
@endpush
