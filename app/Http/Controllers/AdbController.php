<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class AdbController extends Controller
{
    protected $neo4j;
    protected $mongo;

    public function __construct()
    {

    }

    public function cases(int $year, int $diseaseId, int $locId = 0)
    {
        if($locId == 0)
        {
            $result = DB::select('
                SELECT fc.caseCount, dd.diseaseName, LOWER(ld.StateIso) as stateIso
                FROM adb.fact_cases fc
                JOIN adb.time_dim td on td.timeId = fc.timeId
                JOIN adb.loc_dim ld on ld.locId = fc.locId
                JOIN adb.disease_dim dd on dd.diseaseId = fc.diseaseId
                WHERE td.year = :year
                AND dd.diseaseId = :diseaseId',
                ['year' => $year, 'diseaseId' => $diseaseId]
            );
        }
        else
        {
            $result = DB::select('
                SELECT fc.caseCount, dd.diseaseName, LOWER(ld.StateIso) as stateIso
                FROM adb.fact_cases fc
                JOIN adb.time_dim td on td.timeId = fc.timeId
                JOIN adb.loc_dim ld on ld.locId = fc.locId
                JOIN adb.disease_dim dd on dd.diseaseId = fc.diseaseId
                WHERE td.year = :year
                AND dd.diseaseId = :diseaseId
                AND fc.locId = :locId',
                ['year' => $year, 'diseaseId' => $diseaseId, 'locId' => $locId]
            );
        }

        return response()->json($result);
    }
}
