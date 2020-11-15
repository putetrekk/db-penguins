<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TychoDiseaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $diseases = [];

        $row = 0;
        if (($handle = fopen("database/tycho.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($row++ == 0)
                    continue;

                if (in_array($data[0], $diseases))
                    continue;

                $diseases[] = $data[0];

                $data[2] = str_replace('\'', "", $data[2]);
                echo "Adding disease '$data[0]', '$data[1]', '$data[2]', '$data[3]'\n";

                DB::insert("INSERT INTO odb.diseases
                                  (ConditionName, ConditionSNOMED, PathogenName, PathogenTaxonId)
                                  VALUES ('$data[0]', '$data[1]', '$data[2]', '$data[3]')");

                echo "Added disease " . $data[0] . "\n";
            }
            fclose($handle);
        }
    }
}
