<?php

namespace Database\Seeders;

use App\Console\ConsoleHelper;
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
        $output = $this->command->getOutput();
        $bar = ConsoleHelper::ProgressBar($output, 10);

        DB::table("adb.fact_cases")->delete();
        DB::table("adb.fact_fatalities")->delete();
        DB::table("adb.disease_dim")->delete();
        DB::table("adb.loc_dim")->delete();
        DB::table("adb.time_dim")->delete();

        $bar->advance(1);

        DB::insert('
            insert into adb.disease_dim
            (
                select d.Id, d.ConditionName
                from odb.diseases d
            )
        ');

        $bar->advance(1);

        DB::insert('
            insert into adb.loc_dim (StateName, StateIso, CountryName)
            (
                select distinct l.StateName, l.StateIso, l.CountryName
                from odb.locations l
            )
        ');

        $bar->advance(1);

        DB::insert('
            insert into adb.time_dim (year)
            (
                select distinct YEAR(c.PeriodEnd)
                from odb.cases c
            )
        ');

        $bar->advance(1);

        DB::insert('
            insert into adb.fact_cases (timeId, locId, diseaseId, caseCount)
            (
                select t.timeId, al.locId, c.DiseaseId, SUM(c.CountValue)
                from odb.cases c
                join odb.locations ol on c.LocationId = ol.Id
                join adb.loc_dim al on ol.StateName = al.StateName
                join adb.time_dim t on YEAR(c.PeriodEnd) = t.year
                where c.Fatalities = false
                group by locId, t.timeId, diseaseId
            )
        ');

        $bar->advance(3);

        DB::insert('
            insert into adb.fact_fatalities (timeId, locId, diseaseId, fatalitiesCount)
            (
                select t.timeId, al.locId, c.DiseaseId, SUM(c.CountValue)
                from odb.cases c
                join odb.locations ol on c.LocationId = ol.Id
                join adb.loc_dim al on ol.StateName = al.StateName
                join adb.time_dim t on YEAR(c.PeriodEnd) = t.year
                where c.Fatalities = true
                group by locId, t.timeId, diseaseId
            )
        ');

        $bar->advance(3);
        $output->newLine();
    }
}
