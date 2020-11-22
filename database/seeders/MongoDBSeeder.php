<?php

namespace Database\Seeders;

use App\Console\ConsoleHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Services\MongoDBService;

class MongoDBSeeder extends Seeder
{
    /**
     * @var \MongoDB\Client
     */
    private $mongo;
    /**
     * @var \Illuminate\Console\OutputStyle
     */
    private $output;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->output = $this->command->getOutput();
        $this->mongo = (new MongoDBService())->Client();

        $this->output->text("<comment>Clearing MongoDB database...</comment>");
        $this->mongo->dropDatabase('tycho');
        $this->output->text("<comment>Done!</comment>");

        $this->loadDiseases();
        $this->loadAdbData();
    }

    public function loadAdbData()
    {
        $this->output->newLine();
        $this->output->text("<comment>Importing ADB data...</comment>");

        $casesAndFatalitiesByYear = DB::select('
            SELECT y.year, y.StateName, d.ConditionName, y.cases, y.fatalities
            FROM (SELECT year, DiseaseId, StateName, SUM(cases) as cases, SUM(fatalities) as fatalities
                  FROM
                     ((SELECT YEAR(PeriodEnd) as year, DiseaseId, l.StateName, SUM(CountValue) as cases, 0 as fatalities
                      FROM odb.cases
                      JOIN odb.locations l on l.Id = cases.LocationId
                      where Fatalities = 0
                      GROUP BY YEAR(PeriodEnd), DiseaseId, l.StateName, Fatalities)
                    UNION
                     (SELECT YEAR(PeriodEnd) as year, DiseaseId, l.StateName, 0 as cases, SUM(CountValue) as fatalities
                      FROM odb.cases
                      JOIN odb.locations l on l.Id = cases.LocationId
                      where Fatalities = 1
                      GROUP BY YEAR(PeriodEnd), DiseaseId, l.StateName, Fatalities)) unionTable
                  GROUP BY year, DiseaseId, StateName) y
            JOIN odb.diseases d ON d.Id = y.DiseaseId
        ');

        $progress = ConsoleHelper::ProgressBar($this->output, count($casesAndFatalitiesByYear));
        $progress->start();
        $collection = $this->mongo->tycho->fact;
        foreach ($progress->iterate($casesAndFatalitiesByYear) as $singleYear)
        {
            $collection->insertOne([
                'year' => $singleYear->year,
                'StateName'=> $singleYear->StateName,
                'ConditionName' => $singleYear->ConditionName,
                'cases' => $singleYear->cases,
                'fatalities' => $singleYear->fatalities
            ]);
        }
        $this->output->newLine();
    }

    public function loadDiseases()
    {
        // used for list at test.homestead/demo
        $this->output->newLine();
        $this->output->text("<comment>Loading Diseases...</comment>");

        $diseases = DB::select('
            SELECT Id, ConditionSNOMED, ConditionName, PathogenName, PathogenTaxonId
            FROM odb.diseases
        ');

        $collection = $this->mongo->tycho->diseases;
        foreach ($diseases as $disease)
        {
            $collection->insertOne([
                'ConditionSNOMED' => $disease->ConditionSNOMED,
                'ConditionName' => $disease->ConditionName,
                'PathogenName' => $disease->PathogenName,
                'PathogenTaxonId' => $disease->PathogenTaxonId,
            ]);
        }
    }
}
