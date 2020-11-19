<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TychoCasesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Load keys for foreign key relations
        $diseases = DB::select("SELECT Id, ConditionSNOMED FROM odb.diseases");
        $diseaseMap = [];
        foreach ($diseases as $disease)
            $diseaseMap[$disease->ConditionSNOMED] = $disease->Id;

        $locations = DB::select("SELECT Id, CONCAT(CountryName, CountryIso, StateName, StateISO, CountyName, CityName) AS 'key' FROM odb.locations");
        $locationMap = [];
        foreach ($locations as $location)
            $locationMap[$location->key] = $location->Id;

        $sources = DB::select("SELECT Id, SourceName FROM odb.sources");
        $sourceMap = [];
        foreach ($sources as $source)
            $sourceMap[$source->SourceName] = $source->Id;

        // Parse CSV and insert cases
        $caseCount = 0;
        $cases = [];
        $row = 0;
        if (($handle = fopen("data/tycho.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($row++ == 0)
                    continue; // Skip header

                $periodStart = $data[11];
                $periodEnd = $data[12];
                $fatalities = $data[4];
                $count = (int)$data[19];

                $diseaseId = $diseaseMap[$data[1]];
                $locationId = $locationMap[$data[5].$data[6].$data[7].$data[8].$data[9].$data[10]];
                $sourceId = $sourceMap[$data[18]];

                $cases[] = [$periodStart, $periodEnd, $fatalities, $count, $diseaseId, $locationId, $sourceId];

                if (count($cases) >= 10000)
                {
                    $this->insertCases($cases);
                    $caseCount += count($cases);
                    $cases = [];
                    echo "$caseCount cases added.\n";
                }
            }
            fclose($handle);
        }
        if (count($cases) > 0)
        {
            $this->insertCases($cases);
            $caseCount += count($cases);
            echo "$caseCount cases added.\n";
        }
    }

    private function insertCases($cases)
    {
        $sql_values = array_map(function($case) {
            return "('$case[0]', '$case[1]', $case[2], $case[3], $case[4], $case[5], $case[6])";
        }, $cases);

        $sql_str = implode(",", $sql_values);

        DB::insert("INSERT INTO odb.cases
                          (PeriodStart, PeriodEnd, Fatalities, CountValue, DiseaseId, LocationId, SourceId)
                          VALUES $sql_str");
    }
}
