<?php

namespace App\Jobs;

use App\Models\TaskFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessUploadTaskFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $full_path_to_original,
        public string $original_file_name,
        public int $file_size,
        public int $taskId,
        public int $uploaderId,
    ) {}

    public function handle(): void
    {
        TaskFile::create([
            'task_id' => $this->taskId,
            'uploaded_by' => $this->uploaderId,
            'file_name' => $this->original_file_name,
            'file_path' => $this->full_path_to_original,
            'file_size' => $this->file_size,
        ]);
    }
}
