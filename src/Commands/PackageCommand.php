<?php

namespace Fastchartdev\Package\Commands;

use Illuminate\Console\Command;

class PackageCommand extends Command
{
    public $signature = 'package';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
