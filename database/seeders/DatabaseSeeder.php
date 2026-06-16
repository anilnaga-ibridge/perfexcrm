<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Client;
use App\Models\Contact;
use App\Models\LeadStatus;
use App\Models\LeadSource;
use App\Models\Lead;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Admin User
        $user = User::create([
            'name' => 'Armando Turcotte',
            'email' => 'admin@test.com',
            'password' => Hash::make('admin'),
        ]);

        // 2. Seed Lead Statuses
        $newStatus = LeadStatus::create(['name' => 'New', 'color' => '#64748b', 'order_num' => 1]);
        $contacted = LeadStatus::create(['name' => 'Contacted', 'color' => '#3b82f6', 'order_num' => 2]);
        $working = LeadStatus::create(['name' => 'Working', 'color' => '#e11d48', 'order_num' => 3]);
        $qualified = LeadStatus::create(['name' => 'Qualified', 'color' => '#8b5cf6', 'order_num' => 4]);
        $proposalSent = LeadStatus::create(['name' => 'Proposal Sent', 'color' => '#ec4899', 'order_num' => 5]);
        $customerStatus = LeadStatus::create(['name' => 'Customer', 'color' => '#10b981', 'order_num' => 6]);
        $lost = LeadStatus::create(['name' => 'Lost Leads', 'color' => '#ef4444', 'order_num' => 7]);
        
        // 3. Seed Lead Sources
        $google = LeadSource::create(['name' => 'Google']);
        $facebook = LeadSource::create(['name' => 'Facebook']);
        $referral = LeadSource::create(['name' => 'Referral']);
        $website = LeadSource::create(['name' => 'Website Form']);
        $coldCall = LeadSource::create(['name' => 'Cold Call']);

        // 4. Seed Customer (Client)
        $client1 = Client::create([
            'company' => 'Hauck Inc',
            'phonenumber' => '123-456-7890',
            'website' => 'https://hauck.example',
            'address' => '123 Business Rd',
            'city' => 'New York',
            'state' => 'NY',
            'zip' => '10001',
            'country' => 'United States',
        ]);
        Contact::create([
            'client_id' => $client1->id,
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john@hauck.example',
            'phonenumber' => '123-456-7890',
            'title' => 'CEO',
            'is_primary' => true,
        ]);

        $client2 = Client::create([
            'company' => 'Bins-Friesen',
            'phonenumber' => '987-654-3210',
            'website' => 'https://binsfriesen.example',
            'address' => '456 Industrial Way',
            'city' => 'Los Angeles',
            'state' => 'CA',
            'zip' => '90001',
            'country' => 'United States',
        ]);
        Contact::create([
            'client_id' => $client2->id,
            'firstname' => 'Sarah',
            'lastname' => 'Friesen',
            'email' => 'sarah@binsfriesen.example',
            'phonenumber' => '987-654-3210',
            'title' => 'Marketing Manager',
            'is_primary' => true,
        ]);

        // 5. Seed Leads
        Lead::create([
            'name' => 'Jane Smith',
            'title' => 'Director',
            'company' => 'Smith & Co',
            'email' => 'jane@smith.example',
            'phonenumber' => '987-654-3210',
            'lead_value' => 5000.00,
            'status_id' => $newStatus->id,
            'source_id' => $google->id,
            'assigned_id' => $user->id,
        ]);

        Lead::create([
            'name' => 'Robert Johnson',
            'title' => 'Operations Manager',
            'company' => 'Johnson Logistics',
            'email' => 'robert@johnson.example',
            'phonenumber' => '444-555-6666',
            'lead_value' => 12000.00,
            'status_id' => $contacted->id,
            'source_id' => $referral->id,
            'assigned_id' => $user->id,
        ]);

        Lead::create([
            'name' => 'Michael Brown',
            'title' => 'Tech Lead',
            'company' => 'Brown Tech Solutions',
            'email' => 'michael@browntech.example',
            'phonenumber' => '222-333-4444',
            'lead_value' => 7500.00,
            'status_id' => $working->id,
            'source_id' => $website->id,
            'assigned_id' => $user->id,
        ]);
    }
}
