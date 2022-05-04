<?php

namespace App\Services\Image;

use App\Services\Contracts\Image\ImageService as ImageServiceContract;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Intervention\Image\Image as ImageInstance;

class ImageService implements ImageServiceContract
{
    public function save(UploadedFile $file, $directory, $name = null)
    {
        Storage::makeDirectory($directory);

        $fileName = $name ?: $this->generateName($file);
        $path = $directory . "/" . $fileName;

        $img = Image::make($file);
        Storage::put($path, $this->stripImage($img));

        return $path;
    }

    public function delete($path)
    {
        $exists = false;

        if ($path != null) {
            $exists = Storage::exists($path);
            Storage::delete($path);
        }

        return $exists;
    }

    protected function stripImage(ImageInstance $img)
    {
        $img->getCore()->stripImage();
        $img->encode(null, 80);

        return $img;
    }

    protected function generateName(UploadedFile $file)
    {
        return Str::random(40) . "." . Str::lower($file->getClientOriginalExtension());
    }
}
