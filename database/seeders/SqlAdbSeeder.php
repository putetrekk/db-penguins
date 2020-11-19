<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SqlAdbSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("adb.fact_cases")->delete();
        DB::table("adb.fact_fatalities")->delete();
        DB::table("adb.disease_dim")->delete();
        DB::table("adb.loc_dim")->delete();
        DB::table("adb.time_dim")->delete();

        DB::insert("
        insert into adb.disease_dim (
            select d.Id, d.ConditionName
            from odb.diseases d
        );");

        DB::insert("
        insert into adb.loc_dim
        (locName) (
            select distinct l.StateName
            from odb.locations l
        );");

        DB::insert("
        insert into adb.time_dim
        (year) (
            select distinct YEAR(c.PeriodEnd)
            from odb.cases c
        );");

        DB::insert("
        insert into adb.fact_cases
        (timeId, locId, diseaseId, caseCount) (
            select t.timeId, al.locId, c.DiseaseId, SUM(c.CountValue)
            from odb.cases c
                join odb.locations ol on c.LocationId = ol.Id
                join adb.loc_dim al on ol.StateName = al.locName
                join adb.time_dim t on YEAR(c.PeriodEnd) = t.year
            where c.Fatalities = false
            group by locId, t.timeId, diseaseId
        );");

        DB::insert("
        insert into adb.fact_fatalities
        (timeId, locId, diseaseId, fatalitiesCount) (
            select t.timeId, al.locId, c.DiseaseId, SUM(c.CountValue)
            from odb.cases c
                join odb.locations ol on c.LocationId = ol.Id
                join adb.loc_dim al on ol.StateName = al.locName
                join adb.time_dim t on YEAR(c.PeriodEnd) = t.year
            where c.Fatalities = true
            group by locId, t.timeId, diseaseId
        );");
    }
}
