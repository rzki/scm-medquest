<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = User::create([
            'userId' => Str::orderedUuid(),
            'name' => 'Superadmin',
            'initial' => 'SAM',
            'email' => 'superadmin@medquest.co.id',
            'password' => Hash::make('Superadmin2025!')
        ]);
        $superadmin->assignRole('Super Admin');

        $supplyChainManager = User::create([
            'userId' => Str::orderedUuid(),
            'name' => 'Supply Chain Manager',
            'initial' => 'SCM',
            'email' => 'scm@medquest.co.id',
            'password' => Hash::make('Scm2025!')
        ]);
        $supplyChainManager->assignRole('Supply Chain Manager');

        $qaManager = User::create([
            'userId' => Str::orderedUuid(),
            'name' => 'QA Manager',
            'initial' => 'QAM',
            'email' => 'qam@medquest.co.id',
            'password' => Hash::make('Scm2025!')
        ]);
        $qaManager->assignRole('QA Manager');

        $sco = User::create([
            'userId' => Str::orderedUuid(),
            'name' => 'Supply Chain Officer',
            'initial' => 'SCO',
            'email' => 'sco@medquest.co.id',
            'password' => Hash::make('Scm2025!')
        ]);
        $sco->assignRole('Supply Chain Officer');

        $qas = User::create([
            'userId' => Str::orderedUuid(),
            'name' => 'QA Staff',
            'initial' => 'QAS',
            'email' => 'qas@medquest.co.id',
            'password' => Hash::make('Scm2025!')
        ]);
        $qas->assignRole('QA Staff');

        $sec = User::create([
            'userId' => Str::orderedUuid(),
            'name' => 'Security',
            'initial' => 'SEC',
            'email' => 'sec@medquest.co.id',
            'password' => Hash::make('Scm2025!')
        ]);
        $sec->assignRole('Security');
    }
}
