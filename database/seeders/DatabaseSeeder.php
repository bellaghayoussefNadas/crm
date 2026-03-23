<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Client;
use App\Models\Lead;
use Spatie\Permission\Models\Role;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Setup Tenant
        $tenant = \App\Models\Tenant::create([
            'name' => 'Demo Company',
            'domain' => 'demo.crm.test',
            'subscription_plan' => 'enterprise'
        ]);

        // Setup Roles
        $adminRole = Role::create(['name' => 'Admin']);
        $managerRole = Role::create(['name' => 'Manager']);
        $userRole = Role::create(['name' => 'User']);

        $admin = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Admin User',
            'email' => 'admin@crm.test',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole($adminRole);

        $manager = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Manager User',
            'email' => 'manager@crm.test',
            'password' => Hash::make('password'),
        ]);
        $manager->assignRole($managerRole);

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Sales User',
            'email' => 'user@crm.test',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole($userRole);

        // Dummy Account and Contact
        $account = \App\Models\Account::create([
            'tenant_id' => $tenant->id,
            'name' => 'Acme Global',
            'industry' => 'Software',
            'size' => '500-1000'
        ]);

        $contact = \App\Models\Contact::create([
            'tenant_id' => $tenant->id,
            'account_id' => $account->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@acme.com',
            'job_title' => 'CTO'
        ]);

        // Dummy Deal
        \App\Models\Deal::create([
            'tenant_id' => $tenant->id,
            'account_id' => $account->id,
            'contact_id' => $contact->id,
            'name' => 'Licence SaaS 100 users',
            'amount' => 15000,
            'stage' => 'negotiation',
            'probability' => 60
        ]);

        // Old tables keeping for compatibility
        Client::create([
            'tenant_id' => $tenant->id,
            'name' => 'Acme Corp',
            'email' => 'contact@acme.com',
            'phone' => '+123456789',
            'company' => 'Acme Corp',
            'address' => '123 Business St',
            'notes' => 'Top client'
        ]);

        Lead::create([
            'tenant_id' => $tenant->id,
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone' => '+987654321',
            'company' => 'Startup Inc',
            'status' => 'new',
            'user_id' => $user->id
        ]);
    }
}
