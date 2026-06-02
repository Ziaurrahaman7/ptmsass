<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Superadmin — কোনো company নেই
        User::create([
            'name'              => 'Super Admin',
            'email'             => 'superadmin@ptmsaas.com',
            'password'          => 'superadmin123',
            'role'              => 'superadmin',
            'company_id'        => null,
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        // 2. Demo Company তৈরি
        $company = Company::create([
            'name'          => 'Demo Company',
            'slug'          => 'demo-company',
            'email'         => 'info@democompany.com',
            'phone'         => '+8801700000000',
            'status'        => 'active',
            'trial_ends_at' => now()->addDays(30),
        ]);

        // 3. Company Admin
        User::create([
            'name'              => 'Company Admin',
            'email'             => 'admin@democompany.com',
            'password'          => 'admin123',
            'role'              => 'company_admin',
            'company_id'        => $company->id,
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        // 4. Employee
        User::create([
            'name'              => 'John Employee',
            'email'             => 'employee@democompany.com',
            'password'          => 'employee123',
            'role'              => 'employee',
            'company_id'        => $company->id,
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);
    }
}
