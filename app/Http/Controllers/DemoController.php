<?php

namespace App\Http\Controllers;


use App\Services\Neo4JDB;
use App\Services\MongoDBService;

class DemoController extends Controller
{
    protected $db;
    protected $mongo;

    public function __construct(Neo4JDB $neo4j, MongoDBService $mongo)
    {
        $this->db = $neo4j->Client();
        $this->mongo = $mongo->Client();
    }

    public function show()
    {
        $result = $this->db->run("
            MATCH (d:Disease)
            RETURN d.ConditionName as name
        ");

        $diseaseNames = [];
        foreach ($result->records() as $record)
        {
            $diseaseNames[] = $record->get('name');
        }

        return View("demo", ["diseases" => $diseaseNames]);
    }
}
