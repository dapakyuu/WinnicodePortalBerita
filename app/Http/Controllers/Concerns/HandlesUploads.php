<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/** Simpan & hapus file unggahan di disk "public". */
trait HandlesUploads
{
    protected function upload(UploadedFile $file, string $folder): string
    {
        return $file->store($folder, 'public');
    }

    /** Hapus file unggahan lama (aset bawaan di folder images/ dan URL luar tidak ikut dihapus). */
    protected function deleteUpload(?string $path): void
    {
        if ($path && ! str_starts_with($path, 'images/') && ! str_starts_with($path, 'http')) {
            Storage::disk('public')->delete($path);
        }
    }
}
