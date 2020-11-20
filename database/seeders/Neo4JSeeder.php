<?php

namespace Database\Seeders;

use App\Services\Neo4JDBService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Console\ConsoleHelper;

class Neo4JSeeder extends Seeder
{
    /**
     * @var \GraphAware\Neo4j\Client\ClientInterface
     */
    private $neo4j;

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

        $this->neo4j = (new Neo4JDBService())->Client();

        $this->output->text("<comment>Clearing Neo4J database...</comment>");

        $this->neo4j->run(
            /** @lang Cypher */'
            MATCH (n) DETACH DELETE n
        ');
        $this->output->text("<info>Done!</info>");

        $this->loadDiseases();
        $this->loadLocations();
        $this->loadCases();
    }

    private function loadDiseases()
    {
        $this->output->newLine();
        $this->output->text("<comment>Importing diseases...</comment>");

        $diseases = DB::select('
            SELECT ConditionName, PathogenName
            FROM odb.diseases
        ');

        $progress = ConsoleHelper::ProgressBar($this->output, count($diseases));
        $progress->start();

        foreach ($progress->iterate($diseases) as $disease)
        {
            $this->neo4j->run(
                /** @lang Cypher */
                'CREATE (d:Disease {ConditionName: {ConditionName}, PathogenName: {PathogenName}})',
                [
                    'ConditionName' => $disease->ConditionName,
                    'PathogenName' => $disease->PathogenName,
                ]
            );
        }
        $this->output->newLine();
    }

    private function loadLocations()
    {
        $this->output->text("<comment>Importing locations...</comment>");

        $locations = DB::select('
            SELECT DISTINCT StateName, StateIso
            FROM odb.locations
        ');

        $progress = ConsoleHelper::ProgressBar($this->output, count($locations));
        $progress->start();

        foreach ($progress->iterate($locations) as $location)
        {
            $this->neo4j->run(
                /** @lang Cypher */
                'CREATE (l:Location {StateIso: {StateIso}, StateName: {StateName}})',
                [
                    'StateIso' => $location->StateIso,
                    'StateName' => $location->StateName,
                ]
            );
        }
        $this->output->newLine();
    }

    private function loadCases()
    {
        $this->output->text("<comment>Importing cases...</comment>");

        $cases = DB::select('
            SELECT YEAR(c.PeriodEnd) as Year, l.StateIso, d.ConditionName, SUM(CountValue) as Value, 1 as Fatalities
            FROM odb.cases c
            JOIN odb.locations l on c.LocationId = l.Id
            JOIN odb.diseases d on c.DiseaseId = d.Id
            WHERE Fatalities=1
            GROUP BY YEAR(c.PeriodEnd), l.StateIso, d.ConditionName
            UNION
            SELECT YEAR(c.PeriodEnd) as Year, l.StateIso, d.ConditionName, SUM(CountValue) as Value, 0 as Fatalities
            FROM odb.cases c
            JOIN odb.locations l on c.LocationId = l.Id
            JOIN odb.diseases d on c.DiseaseId = d.Id
            WHERE Fatalities=0
            GROUP BY YEAR(c.PeriodEnd), l.StateIso, d.ConditionName
        ');

        $progress = ConsoleHelper::ProgressBar($this->output, count($cases));
        $progress->start();
        $progress->setFormat('%current%/%max% [%bar%] %percent:3s%% - %estimated:-6s% remaining');

        $stack = $this->neo4j->stack();
        foreach ($progress->iterate($cases) as $case)
        {
            $params = [
                'ConditionName' => $case->ConditionName,
                'StateIso' => $case->StateIso,
                'Value' => (int)$case->Value,
                'Year' => (int)$case->Year,
            ];

            if ($case->Fatalities) {
                $stack->push(
                    /** @lang Cypher */'
                    MATCH (d:Disease {ConditionName: {ConditionName}}),
                          (l:Location {StateIso: {StateIso}})
                    CREATE (d)-[:KILLED]->(c:Count {Value: {Value}, Year: {Year}})-[:IN]->(l)'
                    , $params);
            }
            else {
                $stack->push(
                    /** @lang Cypher */'
                    MATCH (d:Disease {ConditionName: {ConditionName}}),
                          (l:Location {StateIso: {StateIso}})
                    CREATE (d)-[:INFECTED]->(c:Count {Value: {Value}, Year: {Year}})-[:IN]->(l)'
                    , $params);
            }

            if ($stack->size() > 256) {
                $this->neo4j->runStack($stack);
                $stack = $this->neo4j->stack();
            }
        }

        $this->neo4j->runStack($stack);

        $this->output->newLine();
    }
}
