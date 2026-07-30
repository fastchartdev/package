<?php

namespace Fastchartdev\Package;

use Fastchartdev\Package\Commands\PackageCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider as BasePackageServiceProvider;

class PackageServiceProvider extends BasePackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('package')
            ->hasConfigFile('fastchart')
            ->hasViews()
            ->hasMigrations(['001_create_events_table', '002_create_meters_table', '003_create_event_records_table', '004_create_meter_summaries_table'])
            ->hasCommand(PackageCommand::class);
    }
}
