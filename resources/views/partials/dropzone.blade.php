{{-- Area unggah gambar ("Drop image here, paste or + Select"). Parameter: $name, $label, $wide (bool), $current (URL gambar saat ini) --}}
<div class="dropzone-field {{ !empty($wide) ? 'dropzone-field--wide' : '' }}">
    <span class="field__label">{{ $label }}</span>
    <label class="dropzone @error($name) is-invalid @enderror" data-dropzone tabindex="0">
        <input type="file" name="{{ $name }}" accept="image/*" hidden data-dropzone-input>
        <img @if (!empty($current)) src="{{ $current }}" @endif alt="" class="dropzone__preview" data-dropzone-preview @if (empty($current)) hidden @endif>
        <span class="dropzone__content" data-dropzone-content @if (!empty($current)) hidden @endif>
            <i class="fa-regular fa-image dropzone__icon"></i>
            <span class="dropzone__text">
                <span>Drop image here, paste or</span>
                <span class="dropzone__btn"><i class="fa-solid fa-plus"></i> Select</span>
            </span>
        </span>
    </label>
    @error($name)
        <p class="field__error">{{ $message }}</p>
    @enderror
</div>
