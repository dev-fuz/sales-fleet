<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Modules\Core\DatabaseState;
use Modules\Core\Environment;
use Modules\Core\Facades\Innoclapps;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Artisan::call('concord:clear-cache');
        Innoclapps::disableNotifications();

        settings(['_seeded' => false]);

        DatabaseState::seed();

        $this->call(DemoDataSeeder::class);

        Environment::setInstallationDate();
    }
}
