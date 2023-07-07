<?php

namespace Vuetik\VuetikLaravel\Commands;

use Illuminate\Console\Command;

class VuetikLaravelCommand extends Command
{
    public $signature = 'vuetik-laravel';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
