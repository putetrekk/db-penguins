<?php

namespace App\Http\Controllers;

use App\Services\MongoDBService;
use App\Services\Neo4JDBService;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class HistoryController extends Controller
{
    protected $neo4j;
    protected $mongo;

    public function __construct(Neo4JDBService $neo4j, MongoDBService $mongo)
    {
        $this->neo4j = $neo4j->Client();
        $this->mongo = $mongo->Client();
    }

    public function show()
    {
        $diseases = DB::select('SELECT ConditionName FROM odb.diseases');
        $diseaseNames = array_map(function ($d) { return $d->ConditionName; }, $diseases);
        return view('history', ['diseases' => $diseaseNames]);
    }

    public function history(Request $request, string $diseaseName, string $stateIso)
    {
        $diseaseName = urldecode($diseaseName);

        $adb = $request->input('adb', 'sql');

        switch (strtolower($adb))
        {
            case 'sql':
                $result = $this->sqlImplementation($diseaseName, $stateIso);
                break;

            case 'neo4j':
                $result = $this->neo4jImplementation($diseaseName, $stateIso);
                break;

            case 'mongodb':
                $result = $this->mongodbImplementation($diseaseName, $stateIso);
                break;

            default:
                return response("Unsupported database: ${$adb}.", 500);
        };

        return response()->json($result);
    }

    private function mongodbImplementation(string $diseaseName, string $stateIso)
    {
        $collection = $this->mongo->tycho->fact;

        if (strtolower($stateIso) === 'usa') {
            $cursor = $collection->aggregate([
                ['$match' => ['ConditionName' => $diseaseName]],
                ['$group' => ['_id' => '$year', 'cases' => ['$sum' => '$cases'], 'fatalities' => ['$sum' => '$fatalities']]],
                ['$set' => ['year' => '$_id']],
            ]);
        }
        else {
            $cursor = $collection->find(['StateIso' => strtoupper($stateIso), 'ConditionName' => $diseaseName]);
        }

        $result = [];
        foreach ($cursor as $document)
        {
            $result[] = [
                'year' => $document['year'],
                'caseCount' => $document['cases'],
                'deathCount' => $document['fatalities'],
            ];
        }

        return $result;
    }

    private function neo4jImplementation(string $diseaseName, string $stateIso)
    {
        if (strtolower($stateIso) === 'usa') {
            $result = $this->neo4j->run(
                /* @lang Cypher */ "
                MATCH (d:Disease)-[:INFECTED]->(c:Count)
                 WHERE d.ConditionName = {diseaseName}
                 WITH c.Year AS year, sum(c.Value) AS caseCount
                RETURN year, caseCount",
                ['diseaseName' => $diseaseName]
            );
        }
        else {
            $result = $this->neo4j->run(
                /* @lang Cypher */"
                MATCH (d:Disease)-[:INFECTED]->(c:Count)-[:IN]->(l:Location)
                 WHERE d.ConditionName = {diseaseName}
                   AND l.StateIso = {stateIso}
                RETURN c.Year as year, c.Value as caseCount",
                ['diseaseName' => $diseaseName, 'stateIso' => strtoupper($stateIso)]
            );
        }

        return array_map(function ($record) {
            return [
                'year' => $record->get('year'),
                'caseCount' => $record->get('caseCount'),
            ];
        }, $result->records());
    }

    private function sqlImplementation(string $diseaseName, string $stateIso)
    {
        if (strtolower($stateIso) === 'usa') {
            return DB::select('
                SELECT td.year, CAST(SUM(fc.caseCount) AS UNSIGNED) as caseCount
                FROM adb.fact_cases fc
                JOIN adb.time_dim td on td.timeId = fc.timeId
                JOIN adb.loc_dim ld on ld.locId = fc.locId
                JOIN adb.disease_dim dd on dd.diseaseId = fc.diseaseId
                WHERE dd.diseaseName = :diseaseName
                GROUP BY td.year',
                ['diseaseName' => $diseaseName]
            );
        }

        return DB::select('
            SELECT td.year, fc.caseCount
            FROM adb.fact_cases fc
            JOIN adb.time_dim td on td.timeId = fc.timeId
            JOIN adb.loc_dim ld on ld.locId = fc.locId
            JOIN adb.disease_dim dd on dd.diseaseId = fc.diseaseId
            WHERE ld.StateIso = :stateIso
              AND dd.diseaseName = :diseaseName',
            ['diseaseName' => $diseaseName, 'stateIso' => strtoupper($stateIso)]
        );
    }
}
