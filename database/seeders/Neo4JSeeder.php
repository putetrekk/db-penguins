<?php

namespace Database\Seeders;

use App\Services\Neo4JDB;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Neo4JSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $diseases = DB::select('
            SELECT Id, ConditionSNOMED, ConditionName, PathogenName, PathogenTaxonId
            FROM odb.diseases
        ');

        $neo4j = (new Neo4JDB())->Client();

        foreach ($diseases as $disease)
        {
            $neo4j->run('
                CREATE (d:Disease {
                    ConditionSNOMED: {ConditionSNOMED},
                    ConditionName: {ConditionName},
                    PathogenName: {PathogenName},
                    PathogenTaxonId : {PathogenTaxonId}
                })',
                [
                    'ConditionSNOMED' => $disease->ConditionSNOMED,
                    'ConditionName' => $disease->ConditionName,
                    'PathogenName' => $disease->PathogenName,
                    'PathogenTaxonId' => $disease->PathogenTaxonId
                ]
            );
        }
    }
}
