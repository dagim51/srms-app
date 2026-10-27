<?php

namespace App\Jobs;

use App\Models\PropertyMedia;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class ProcessPropertyMedia implements ShouldQueue
{
    use Queueable;

    public $media;

    public function __construct(PropertyMedia $media)
    {
        $this->media = $media;
    }

    public function handle(): void
    {
        $width = 1920;
        $height = 1080;

        // If the media is an image and file exists on disk, apply watermark
        if ($this->media->kind === 'image' && ! empty($this->media->storage_key)) {
            $disk = Storage::disk('public');
            if ($disk->exists($this->media->storage_key)) {
                $filePath = $disk->path($this->media->storage_key);

                if (function_exists('imagecreatefromstring') && file_exists($filePath)) {
                    $imageData = @file_get_contents($filePath);
                    if ($imageData !== false && $imageData !== '') {
                        $image = @imagecreatefromstring($imageData);
                        if ($image !== false) {
                            $width = imagesx($image);
                            $height = imagesy($image);

                            // Apply translucent SRMS watermark in bottom right corner
                            $watermarkText = 'SRMS VERIFIED';
                            $fontSize = 4;
                            $fontWidth = imagefontwidth($fontSize) * strlen($watermarkText);
                            $fontHeight = imagefontheight($fontSize);

                            $margin = 20;
                            $boxPadding = 8;
                            $x = max(10, $width - $fontWidth - $margin - ($boxPadding * 2));
                            $y = max(10, $height - $fontHeight - $margin - ($boxPadding * 2));

                            // Semi-transparent badge background
                            $bgAlpha = imagecolorallocatealpha($image, 0, 0, 0, 60);
                            if ($bgAlpha !== false) {
                                imagefilledrectangle(
                                    $image,
                                    $x,
                                    $y,
                                    $x + $fontWidth + ($boxPadding * 2),
                                    $y + $fontHeight + ($boxPadding * 2),
                                    $bgAlpha
                                );
                            }

                            // White text
                            $textColor = imagecolorallocate($image, 255, 255, 255);
                            if ($textColor !== false) {
                                imagestring(
                                    $image,
                                    $fontSize,
                                    $x + $boxPadding,
                                    $y + $boxPadding,
                                    $watermarkText,
                                    $textColor
                                );
                            }

                            // Save watermarked image
                            imagejpeg($image, $filePath, 90);
                            imagedestroy($image);
                        }
                    }
                }
            }
        }

        $this->media->update([
            'status' => 'processed',
            'width' => $width,
            'height' => $height,
        ]);
    }
}
