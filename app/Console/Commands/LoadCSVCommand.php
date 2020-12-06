<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LoadCSVCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dw:load
                            {file : A csv file to load}
                            {--truncate : Whether the job should truncate odb tables before loading the csv}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load disease data from a csv file. Optionally truncate database tables before loading.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');
        $truncate = $this->option('truncate');

        if ($file == null)
        {
            $this->output->error("Missing file parameter!");
            return;
        }

        if (!is_file($file))
        {
            $this->output->error("${$file} is not a file.");
            return;
        }

        if ($truncate && !$this->output->confirm("ODB tables will be cleared. Are you sure you wish to continue?"))
            return;

        if ($truncate)
        {
            $this->output->warning("Dropping ODB tables");
            // Drop and recreate the ODB database
            $odb = file_get_contents("database/create_odb.sql");
            $this->output->text($odb);
            DB::unprepared($odb);
        }

        $this->loadCsv($file);
    }

    protected function loadCsv($file)
    {
        $diseases = DB::select('SELECT Id, ConditionSNOMED from odb.diseases');
        $diseaseMap = [];
        foreach ($diseases as $disease)
            $diseaseMap[$disease->ConditionSNOMED] = $disease->Id;

        $locations = DB::select("SELECT Id, CONCAT(CountryName, CountryIso, StateName, StateIso, CountyName, CityName) as 'location' from odb.locations");
        $locationMap = [];
        foreach ($locations as $location)
            $locationMap[$location->location] = $location->Id;

        $sources = DB::select('SELECT Id, SourceName from odb.sources');
        $sourceMap = [];
        foreach ($sources as $source)
            $sourceMap[$source->SourceName] = $source->Id;

        $lineNumber = 0;
        $caseCount = 0;
        if (($handle = fopen($file, "r")) !== FALSE) {
            $cases = [];
            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($lineNumber++ == 0)
                    continue;

                $partOfCumulativeSeries = (bool)$row[13];
                if ($partOfCumulativeSeries)
                    continue;

                if (!in_array($row[1], array_keys($diseaseMap)))
                {
                    $id = $this->addDisease($row[0], $row[1], $row[2], $row[3]);
                    $diseaseMap[$row[1]] = $id;
                }

                if (!in_array($row[5].$row[6].$row[7].$row[8].$row[9].$row[10], array_keys($locationMap)))
                {
                    $id = $this->addLocation($row[5], $row[6], $row[7], $row[8], $row[9], $row[10]);
                    $locationMap[$row[5].$row[6].$row[7].$row[8].$row[9].$row[10]] = $id;
                }

                if (!in_array($row[18], array_keys($sourceMap)))
                {
                    $id = $this->addSource($row[18]);
                    $sourceMap[$row[18]] = $id;
                }

                $periodStart = $row[11];
                $periodEnd = $row[12];
                $fatalities = $row[4];
                $count = (int)$row[19];

                $diseaseId = $diseaseMap[$row[1]];
                $locationId = $locationMap[$row[5].$row[6].$row[7].$row[8].$row[9].$row[10]];
                $sourceId = $sourceMap[$row[18]];

                $cases[] = [$periodStart, $periodEnd, $fatalities, $count, $diseaseId, $locationId, $sourceId];

                if (count($cases) >= 50000)
                {
                    $this->insertCases($cases);
                    $caseCount += count($cases);
                    $this->output->text(count($cases) . " new cases added - total: ${caseCount}");
                    $cases = [];
                }
            }
            fclose($handle);

            if (count($cases) > 0)
            {
                $this->insertCases($cases);
                $caseCount += count($cases);
                $this->output->text(count($cases) ." new cases added - total: ${caseCount}");
            }
        }

        $this->output->success("Finished loading ${file}!");
    }

    protected function addDisease($conditionName, $conditionSNOMED, $pathogenName, $pathogenTaxonId)
    {
        $pathogenName = str_replace('\'', "", $pathogenName);

        $id = DB::table('odb.diseases')->insertGetId(
          array('ConditionName' => $conditionName,
              'ConditionSNOMED' => $conditionSNOMED,
              'PathogenName' => $pathogenName,
              'PathogenTaxonId' => $pathogenTaxonId)
        );

        $this->output->text("Added disease ${conditionName}, ${conditionSNOMED}, ${pathogenName}, ${pathogenTaxonId}.");
        return $id;
    }

    protected function addLocation($countryName, $countryIso, $stateName, $stateIso, $countyName, $cityName)
    {
        $id = DB::table('odb.locations')->insertGetId(
            array('CountryName' => $countryName,
                'CountryIso' => $countryIso,
                'StateName' => $stateName,
                'StateIso' => $stateIso,
                'CountyName' => $countyName,
                'CityName' => $cityName)
        );

        $this->output->text("Added location ${countryName}, ${countryIso}, ${stateName}, ${stateIso}, ${countyName}, ${cityName}.");
        return $id;
    }

    protected function addSource($sourceName)
    {
        $id = DB::table('odb.sources')->insertGetId(
            array('SourceName' => $sourceName)
        );

        $this->output->text("Added source ${sourceName}.");
        return $id;
    }

    protected function insertCases($cases)
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
