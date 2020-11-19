<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Empty all tables before starting the seeding process
        DB::table('odb.cases')->delete();
        DB::table('odb.diseases')->delete();
        DB::table('odb.locations')->delete();
        DB::table('odb.sources')->delete();

        $this->call([
            TychoDiseaseSeeder::class,
            TychoLocationsSeeder::class,
            TychoSourceSeeder::class,
            TychoCasesSeeder::class
        ]);
    }
}
