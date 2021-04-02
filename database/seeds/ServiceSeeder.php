<?php

use Illuminate\Database\Seeder;
use App\Service;
class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Service::create(['name' => 'credits']);
        Service::create(['name' => 'microcredits']);
        Service::create(['name' => 'avtocredits']);
        Service::create(['name' => 'lombards']);
        Service::create(['name' => 'credits']);
        Service::create(['name' => 'ipoteka']);

    }
}
