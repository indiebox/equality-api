<?php

namespace App\Services\Image\Contracts;

use Illuminate\Http\UploadedFile;

interface ImageServiceContract {
    public function save(UploadedFile $file, $directory, $name = null);

    public function delete($path);
}
