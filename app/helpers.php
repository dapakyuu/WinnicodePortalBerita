<?php

if (! function_exists('media_url')) {
    /**
     * URL untuk gambar yang disimpan di database.
     * - URL penuh (http...) dipakai apa adanya
     * - path "images/..." = aset bawaan di folder public
     * - path lain = file unggahan di disk "public" (storage/app/public)
     */
    function media_url(?string $path, ?string $fallback = null): ?string
    {
        if (! $path) {
            return $fallback ? asset($fallback) : null;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        return asset('storage/' . ltrim($path, '/'));
    }
}

if (! function_exists('short_number')) {
    /** Format angka ringkas: 1500 -> 1.5k, 2300000 -> 2.3M (tanpa ekstensi intl). */
    function short_number(int|float $value): string
    {
        foreach ([1_000_000_000 => 'B', 1_000_000 => 'M', 1_000 => 'k'] as $size => $suffix) {
            if (abs($value) >= $size) {
                return rtrim(rtrim(number_format($value / $size, 1, '.', ''), '0'), '.') . $suffix;
            }
        }

        return (string) $value;
    }
}
