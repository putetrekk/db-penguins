<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TychoLocationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $locations = [];

        $row = 0;
        if (($handle = fopen("data/tycho.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($row++ == 0)
                    continue;

                if (in_array([$data[5], $data[6], $data[7], $data[8], $data[9], $data[10]], $locations))
                    continue;

                $locations[] = [$data[5], $data[6], $data[7], $data[8], $data[9], $data[10]];

                echo "Adding location '$data[5]', '$data[6]', '$data[7]', '$data[8]', '$data[9]', '$data[10]'\n";

                DB::insert("INSERT INTO odb.locations
                                  (CountryName, CountryIso, StateName, StateIso, CountyName, CityName)
                                  VALUES ('$data[5]', '$data[6]', '$data[7]', '$data[8]', '$data[9]', '$data[10]')");

                echo "Added location " . $data[10] . "\n";
            }
            fclose($handle);
        }
    }
}
