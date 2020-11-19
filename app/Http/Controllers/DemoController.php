<?php

namespace App\Http\Controllers;


use App\Services\Neo4JDB;
use App\Services\MongoDBService;

class DemoController extends Controller
{
    protected $neo4j;
    protected $mongo;

    public function __construct(Neo4JDB $neo4j, MongoDBService $mongo)
    {
        $this->neo4j = $neo4j->Client();
        $this->mongo = $mongo->Client();
    }

    public function show()
    {
        $result = $this->neo4j->run("
            MATCH (d:Disease)
            RETURN d.ConditionName as name
        ");

        $neo4jDiseaseNames = [];
        foreach ($result->records() as $record)
        {
            $neo4jDiseaseNames[] = $record->get('name');
        }

        $collection = $this->mongo->tycho->diseases;
        $mongoDiseaseNames = [];
        foreach ($collection->find() as $record)
        {
            $mongoDiseaseNames[] = $record['ConditionName'];
        }

        return View("demo", ["neo4jDiseases" => $neo4jDiseaseNames, "mongoDiseases" => $mongoDiseaseNames]);
    }
}
