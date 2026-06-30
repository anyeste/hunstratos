<?php

namespace Modules\HunFDM\Database\Seeders;

use Illuminate\Database\Seeder;

class HunFdmDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            HunFdmRulesSeeder::class,
            HunFdmSettingsSeeder::class,
        ]);
    }
}
