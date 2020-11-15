<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TychoSourceSeeder extends Seeder
{
    public function run()
    {
        $sources = [];
        $row = 0;
        if (($handle = fopen("data/tycho.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // skip csv header
                if ($row++ == 0)
                    continue;

                // avoid adding duplicate
                if (in_array($data[18], $sources))
                    continue;

                $sources[] = $data[18];
                DB::insert("INSERT INTO odb.sources
                                  (SourceName)
                                  VALUES ('$data[18]')");
                echo "Added source: '$data[18]' (at row: #$row).\n";
            }
            fclose($handle);
        }
    }
}
