<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Submission;

class FixSubmissionFileMeta extends Command
{
    protected $signature = 'submissions:fix-file-meta';
    protected $description = 'Fix missing file_meta for submissions';

    public function handle()
    {
        $submissions = Submission::whereNull('file_meta')->get();
        foreach ($submissions as $sub) {
            $sub->update([
                'file_meta' => [
                    'original_name' => 'Uploaded Document.pdf',
                    'mime_type' => 'application/pdf',
                    'size' => 1024,
                ]
            ]);
        }
        $this->info("Fixed " . $submissions->count() . " submissions.");
    }
}
