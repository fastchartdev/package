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
            ->hasMigration('create_package_table')
            ->hasCommand(PackageCommand::class);
    }
}
