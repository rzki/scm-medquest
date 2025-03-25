<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::create(['name' => 'Super Admin']);
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'QA Staff']);
        Role::create(['name' => 'Qa Supervisor']);
        Role::create(['name' => 'Supply Chain Manager']);
    }
}
