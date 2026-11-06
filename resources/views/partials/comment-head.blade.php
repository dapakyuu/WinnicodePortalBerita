{{-- Kepala komentar: avatar, nama, waktu, tombol Reply/Hapus. Parameter: $comment, $canReply --}}
<div class="comment__head">
    <img src="{{ $comment->user->avatar_url }}" alt="" class="comment__avatar" width="44" height="44">
    <div class="comment__meta">
        <span class="comment__name">{{ $comment->user->name }}</span>
        <span class="comment__date" title="{{ $comment->created_at->format('d M Y H:i') }}">{{ $comment->created_at->format('F d , Y') }} · {{ $comment->created_at->diffForHumans() }}</span>
    </div>
    @if ($canReply)
        @auth
            <button type="button" class="chip-btn comment__reply" data-reply="{{ $comment->id }}" data-reply-to="{{ $comment->user->name }}">
                <i class="fa-solid fa-reply"></i> Reply
            </button>
        @else
            <a href="{{ route('login') }}" class="chip-btn comment__reply"><i class="fa-solid fa-reply"></i> Reply</a>
        @endauth
    @endif
    @if (auth()->check() && (auth()->id() === $comment->user_id || auth()->user()->isAdmin()))
        <form action="{{ route('comments.destroy', $comment) }}" method="POST" data-confirm="Hapus komentar ini?">
            @csrf @method('DELETE')
            <button type="submit" class="chip-btn comment__reply" aria-label="Hapus komentar"><i class="fa-regular fa-trash-can"></i></button>
        </form>
    @endif
</div>
