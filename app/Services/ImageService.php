<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\ImageManagerStatic as Image;

class ImageService
{
    public function __construct(
        private int $quality = 75,
        private int $maxWidth = 1920
    ) {
        Image::configure(['driver' => 'gd']);
    }

    public function storeAsWebp(UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        $directory = trim($directory, '/');
        $path = ltrim($directory . '/' . Str::uuid()->toString() . '.webp', '/');

        try {
            $image = Image::make($file)
                ->orientate();

            if ($this->maxWidth > 0 && $image->width() > $this->maxWidth) {
                $image->resize($this->maxWidth, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            $encoded = $image->encode('webp', $this->quality);
            Storage::disk($disk)->put($path, (string) $encoded);
            $image->destroy();

            return $path;
        } catch (NotReadableException) {
            return $file->store($directory, $disk);
        }
    }
}

