<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MascotAssetUploadService
{
    public function store(UploadedFile $file, string $field): string
    {
        if (!extension_loaded('gd')) {
            throw ValidationException::withMessages([$field => t('mascot.admin.gd_required')]);
        }
        $size = @getimagesize($file->getRealPath());
        if (!$size || $size[2] !== IMAGETYPE_PNG || [$size[0], $size[1]] !== config('mascot.canvas', [1254, 1254])) {
            throw ValidationException::withMessages([$field => t('mascot.admin.invalid_png')]);
        }
        $source = @imagecreatefrompng($file->getRealPath());
        if (!$source) {
            throw ValidationException::withMessages([$field => t('mascot.admin.invalid_png')]);
        }
        $path = 'images/mascot/catalog/' . Str::uuid() . '.png';
        try {
            if (!imageistruecolor($source) && !imagepalettetotruecolor($source)) {
                throw ValidationException::withMessages([$field => t('mascot.admin.invalid_png')]);
            }
            $clear = false;
            $nonOpaque = false;
            $visible = false;
            for ($y = 0; $y < $size[1] && ($field === 'background' ? !$nonOpaque : !($clear && $visible)); $y++) {
                for ($x = 0; $x < $size[0] && ($field === 'background' ? !$nonOpaque : !($clear && $visible)); $x++) {
                    $alpha = (imagecolorat($source, $x, $y) >> 24) & 127;
                    $clear = $clear || $alpha === 127;
                    $nonOpaque = $nonOpaque || $alpha !== 0;
                    $visible = $visible || $alpha < 127;
                }
            }
            if ($field === 'background' ? $nonOpaque : (!$clear || !$visible)) {
                throw ValidationException::withMessages([$field => t('mascot.admin.invalid_png')]);
            }
            imagealphablending($source, false);
            imagesavealpha($source, true);
            File::ensureDirectoryExists(public_path('images/mascot/catalog'));
            if (!imagepng($source, public_path($path), 6)) {
                throw ValidationException::withMessages([$field => t('mascot.admin.upload_failed')]);
            }
            return $path;
        } catch (\Throwable $error) {
            File::delete(public_path($path));
            throw $error;
        } finally {
            imagedestroy($source);
        }
    }
}
