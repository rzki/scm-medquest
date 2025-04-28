<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Location::create([
            'locationId' => Str::orderedUuid(),
            'location_name' => 'Gudang A35 Bizpark 2',
            'serial_number' => 'Y835PR',
            'temperature_start' => '15',
            'temperature_end' => '30',
        ]);
        Location::create([
            'locationId' => Str::orderedUuid(),
            'location_name' => 'Gudang 3 Bizpark 2',
            'serial_number' => 'T481CA',
            'temperature_start' => '15',
            'temperature_end' => '25',
        ]);
        Location::create([
            'locationId' => Str::orderedUuid(),
            'location_name' => 'Gudang 2 Bizpark 2',
            'serial_number' => 'F14107',
            'temperature_start' => '15',
            'temperature_end' => '25',
        ]);
        Location::create([
            'locationId' => Str::orderedUuid(),
            'location_name' => 'Gudang 1 Bizpark 2',
            'serial_number' => 'F11276',
            'temperature_start' => '15',
            'temperature_end' => '25',
        ]);
        Location::create([
            'locationId' => Str::orderedUuid(),
            'location_name' => 'Ambient Quarantine Bizpark 2',
            'serial_number' => 'C804TS',
            'temperature_start' => '15',
            'temperature_end' => '25',
        ]);
        Location::create([
            'locationId' => Str::orderedUuid(),
            'location_name' => 'Ambient D.3.E.3 Bizpark 2',
            'serial_number' => 'W943GB',
            'temperature_start' => '15',
            'temperature_end' => '25',
        ]);
        Location::create([
            'locationId' => Str::orderedUuid(),
            'location_name' => 'Ambient B.1.A.2 Bizpark 2',
            'serial_number' => 'P530AL',
            'temperature_start' => '15',
            'temperature_end' => '25',
        ]);
        Location::create([
            'locationId' => Str::orderedUuid(),
            'location_name' => 'Air Cond Storage 1',
            'serial_number' => 'B690EK',
            'temperature_start' => '15',
            'temperature_end' => '25',
        ]);
        Location::create([
            'locationId' => Str::orderedUuid(),
            'location_name' => 'Air Cond Storage 2',
            'serial_number' => 'G357QD',
            'temperature_start' => '15',
            'temperature_end' => '30',
        ]);
        Location::create([
            'locationId' => Str::orderedUuid(),
            'location_name' => 'Air Cond Storage 3',
            'serial_number' => ' J596VM',
            'temperature_start' => '15',
            'temperature_end' => '30',
        ]);
        Location::create([
            'locationId' => Str::orderedUuid(),
            'location_name' => 'Cold Storage 1',
            'serial_number' => 'Q647TS',
            'temperature_start' => '2',
            'temperature_end' => '8',
        ]);
        Location::create([
            'locationId' => Str::orderedUuid(),
            'location_name' => 'Cold Storage 2',
            'serial_number' => 'D279FF',
            'temperature_start' => '2',
            'temperature_end' => '8',
        ]);
        Location::create([
            'locationId' => Str::orderedUuid(),
            'location_name' => 'Cold Storage 3',
            'serial_number' => '160618766',
            'temperature_start' => '2',
            'temperature_end' => '8',
        ]);
        Location::create([
            'locationId' => Str::orderedUuid(),
            'location_name' => 'Cold Storage 4',
            'serial_number' => ' PX47102',
            'temperature_start' => '2',
            'temperature_end' => '8',
        ]);
        Location::create([
            'locationId' => Str::orderedUuid(),
            'location_name' => 'Freezer 1',
            'serial_number' => 'F-16778',
            'temperature_start' => '-35',
            'temperature_end' => '-15',
        ]);
        Location::create([
            'locationId' => Str::orderedUuid(),
            'location_name' => 'Freezer Storage',
            'serial_number' => 'F-16779',
            'temperature_start' => '-35',
            'temperature_end' => '-15',
        ]);
        Location::create([
            'locationId' => Str::orderedUuid(),
            'location_name' => 'Refrigerator 1',
            'serial_number' => 'M115HE',
            'temperature_start' => '2',
            'temperature_end' => '8',
        ]);
        Location::create([
            'locationId' => Str::orderedUuid(),
            'location_name' => 'Freezer 6',
            'serial_number' => 'F-14404',
            'temperature_start' => '-25',
            'temperature_end' => '-10',
        ]);
        Location::create([
            'locationId' => Str::orderedUuid(),
            'location_name' => 'Freezer Packaging Storage',
            'serial_number' => 'F-14397',
            'temperature_start' => '-25',
            'temperature_end' => '-10',
        ]);
    }
}
