<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use App\Models\PropertyMedia;

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
        // Simulate processing (generating thumbnails, extracting metadata)
        if ($this->media->status === 'pending') {
            $this->media->update([
                'status' => 'processed',
                'width' => 1920,
                'height' => 1080
            ]);
        }
    }
}
