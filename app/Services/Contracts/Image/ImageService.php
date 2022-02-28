<?php

namespace App\Services\Contracts\Image;

use Illuminate\Http\UploadedFile;

interface ImageService {
    /** Save uploaded image to the storage.
     * @param UploadedFile $file
     * @param string $directory The directory for saving the image.
     * @param string|null $name Image name.
     *
     * @return string Path to saved image.
     */
    public function save(UploadedFile $file, $directory, $name = null);

    /** Delete image from the storage.
     * @param string|null $path Image path.
     *
     * @return boolean
     */
    public function delete($path);
}
