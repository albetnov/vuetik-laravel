<?php

namespace Vuetik\VuetikLaravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Vuetik\VuetikLaravel\Models\VuetikImages;
use Vuetik\VuetikLaravel\Utils;

class PurgeUnusedImages extends Command
{
    public $signature = 'purge:unused-images {--after} {--disk} {--path} {--show-log}';

    public $description = 'Purge all unused images';

    public function handle(): int
    {
        $dateTime = $this->option('after');
        if (!$dateTime) $dateTime = config('vuetik-laravel.purge_after');

        $disk = $this->option('disk');
        if (!$disk) $disk = config('vuetik-laravel.storage.disk');

        $storagePath = $this->option('path');
        if (!$storagePath) $storagePath = config('vuetik-laravel.storage.path');

        $images = VuetikImages::where('status', VuetikImages::PENDING)
            ->where(
                'created_at',
                '<=',
                now()->min($dateTime)
            );

        foreach ($images->lazy() as $image) {
            $filePath = Utils::parseStoragePath($storagePath) . $image->file_name;
            $checkExisting = Storage::disk($disk)->exists($filePath);

            if(!$checkExisting) {
                $this->error('Failed deleting file (not exist) id: '. $image->id);
                continue;
            }

            Storage::disk($disk)->delete($filePath);

            if($this->option('show-log')) {
                $this->info('Purged: '. $image->id);
            }

            $image->delete();

        }

        $this->info("Unused Image Purged!");

        return self::SUCCESS;
    }
}
