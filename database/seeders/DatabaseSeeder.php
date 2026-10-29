<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with synthetic fixtures.
     */
    public function run(): void
    {
        $this->call([
            SyntheticDataSeeder::class,
        ]);
    }
}

