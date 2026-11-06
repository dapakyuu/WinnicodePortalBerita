{{-- Editor teks dengan toolbar format. Parameter: $name, $label, $value --}}
<div class="editor-field">
    <label for="{{ $name }}" class="field__label">{{ $label }}</label>
    <div class="editor">
        <div class="editor__toolbar">
            <button type="button" class="editor__btn" data-editor-action="image" title="Sisipkan gambar dari URL"><i class="fa-regular fa-image"></i> Image</button>
            <button type="button" class="editor__btn" data-editor-action="color" title="Warnai teks terpilih"><i class="fa-solid fa-eye-dropper"></i> Color</button>
            <button type="button" class="editor__btn" data-editor-action="bold" title="Tebalkan teks terpilih"><i class="fa-solid fa-code"></i> Text</button>
            <button type="button" class="editor__btn" data-editor-action="heading" title="Jadikan judul bagian"><i class="fa-solid fa-align-left"></i> Align</button>
            <button type="button" class="editor__btn" data-editor-action="link" title="Sisipkan tautan"><i class="fa-solid fa-link"></i> Link</button>
        </div>
        <textarea id="{{ $name }}" name="{{ $name }}" class="editor__textarea @error($name) is-invalid @enderror" placeholder="Type ...">{{ old($name, $value ?? '') }}</textarea>
        <p class="editor__hint">Pisahkan paragraf dengan baris kosong. Format: <code>**tebal**</code>, <code>## Judul</code>, <code>[teks](https://link)</code>, <code>![gambar](https://url)</code></p>
    </div>
    @error($name)
        <p class="field__error">{{ $message }}</p>
    @enderror
</div>
