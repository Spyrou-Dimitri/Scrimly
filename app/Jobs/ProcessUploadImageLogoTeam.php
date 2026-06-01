<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ProcessUploadImageLogoTeam implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $full_path_to_original,
        public string $new_original_file_name) {}

    public function handle(): void
    {
        $disk = Storage::disk(config('logoTeam.disk'));

        $image = Image::decodeBinary(
            $disk->get($this->full_path_to_original)
        );

        $sizes = config('logoTeam.sizes');
        $jpeg_compression = config('logoTeam.jpeg_compression');
        $variant_pattern = config('logoTeam.variant_pattern');
        $image_type = pathinfo($this->new_original_file_name, PATHINFO_EXTENSION);

        foreach ($sizes as $size) {
            $variant = clone $image;
            $variant->scale($size['width']);

            $path = sprintf($variant_pattern, $size['width'], $size['height']);
            $disk->put(
                $path.'/'.$this->new_original_file_name,
                $variant->encodeUsingFileExtension($image_type, quality: $jpeg_compression),
            );
        }
    }
}
