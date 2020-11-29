<?php

namespace App\Http\Controllers;

use App\Services\MongoDBService;
use App\Services\Neo4JDBService;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class MapController extends Controller
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
        return view('map', ['diseases' => $diseaseNames]);
    }

    public function cases(Request $request, int $year, string $diseaseName)
    {
        $diseaseName = urldecode($diseaseName);

        $adb = $request->input('adb', 'sql');

        switch (strtolower($adb))
        {
            case 'sql':
                $result = $this->sqlImplementation($year, $diseaseName);
                break;

            case 'neo4j':
                $result = $this->neo4jImplementation($year, $diseaseName);
                break;

            case 'mongodb':
                $result = $this->mongodbImplementation($year, $diseaseName);
                break;

            default:
                return response("Unsupported database: ${$adb}.", 500);
        };

        return response()->json($result);
    }

    private function mongodbImplementation(int $year, string $diseaseName)
    {
        $collection = $this->mongo->tycho->fact;
        $cursor = $collection->find(['year' => $year, 'ConditionName' => $diseaseName]);

        $result = [];
        foreach ($cursor as $document)
        {
            $result[] = [
                'stateIso' => strtolower($document['StateIso']),
                'caseCount' => $document['cases'],
                'deathCount' => $document['fatalities'],
            ];
        }

        return $result;
    }

    private function neo4jImplementation(int $year, string $diseaseName)
    {
        $result = $this->neo4j->run(
            /* @lang Cypher */"
            MATCH (d:Disease)-[:INFECTED]->(c:Count)-[:IN]->(l:Location)
             WHERE d.ConditionName = {diseaseName}
               AND c.Year = {year}
             RETURN c.Value as value, toLower(l.StateIso) as stateIso",
            ['diseaseName' => $diseaseName, 'year' => $year]
        );

        return array_map(function ($record) {
            return [
                'stateIso' => $record->get('stateIso'),
                'caseCount' => $record->get('value'),
            ];
        }, $result->records());
    }

    private function sqlImplementation(int $year, string $diseaseName)
    {
        return DB::select('
            SELECT fc.caseCount, dd.diseaseName, LOWER(ld.StateIso) as stateIso
            FROM adb.fact_cases fc
            JOIN adb.time_dim td on td.timeId = fc.timeId
            JOIN adb.loc_dim ld on ld.locId = fc.locId
            JOIN adb.disease_dim dd on dd.diseaseId = fc.diseaseId
            WHERE td.year = :year
            AND dd.diseaseName = :diseaseName',
            ['year' => $year, 'diseaseName' => $diseaseName]
        );
    }
}
