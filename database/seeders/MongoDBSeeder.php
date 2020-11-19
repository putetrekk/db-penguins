<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Services\MongoDBService;

class MongoDBSeeder extends Seeder
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

        $client = (new MongoDBService())->Client();
        $client->dropDatabase('tycho');
        $collection = $client->tycho->diseases;

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
