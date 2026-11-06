{{--
    Field form (komponen "input" di Figma): label + kotak abu-abu 48px.
    Parameter: $name, $label, $type (default text), $value, $placeholder, $class, $autocomplete
--}}
@php($type = $type ?? 'text')
<div class="field {{ $class ?? '' }}">
    <label for="{{ $name }}" class="field__label">{{ $label }}</label>
    <input
        type="{{ $type }}"
        id="{{ $name }}"
        name="{{ $name }}"
        class="field__input @error($name) is-invalid @enderror"
        @if ($type !== 'password') value="{{ old($name, $value ?? '') }}" @endif
        placeholder="{{ $placeholder ?? '' }}"
        @isset($autocomplete) autocomplete="{{ $autocomplete }}" @endisset
    >
    @error($name)
        <p class="field__error">{{ $message }}</p>
    @enderror
</div>
