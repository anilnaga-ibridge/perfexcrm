<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // ── Staff Members ────────────────────────────────────────
        $staffMembers = [
            ['name' => 'Armando Turcotte', 'email' => 'admin@test.com',        'role' => 'admin',   'active' => 1, 'phone' => '+1-555-0100', 'department' => 'Management'],
            ['name' => 'Elias Konopelski', 'email' => 'elias@company.com',      'role' => 'staff',   'active' => 1, 'phone' => '+1-555-0101', 'department' => 'Sales'],
            ['name' => 'Tamara Howell',    'email' => 'tamara@company.com',     'role' => 'staff',   'active' => 1, 'phone' => '+1-555-0102', 'department' => 'Support'],
            ['name' => 'Marcus Lesch',     'email' => 'marcus@company.com',     'role' => 'manager', 'active' => 1, 'phone' => '+1-555-0103', 'department' => 'Development'],
            ['name' => 'Rosie Trantow',    'email' => 'rosie@company.com',      'role' => 'staff',   'active' => 1, 'phone' => '+1-555-0104', 'department' => 'Finance'],
        ];

        foreach ($staffMembers as $staff) {
            $exists = DB::table('users')->where('email', $staff['email'])->exists();
            if (!$exists) {
                DB::table('users')->insert([
                    'name'               => $staff['name'],
                    'email'              => $staff['email'],
                    'role'               => $staff['role'],
                    'active'             => $staff['active'],
                    'phone'              => $staff['phone'],
                    'department'         => $staff['department'],
                    'password'           => Hash::make('Admin1234!'),
                    'email_verified_at'  => now(),
                    'created_at'         => now()->subDays(rand(30, 180)),
                    'updated_at'         => now(),
                ]);
            } else {
                // update admin role if already exists
                DB::table('users')->where('email', $staff['email'])->update(['role' => $staff['role']]);
            }
        }

        // ── Clients ──────────────────────────────────────────────
        $companies = [
            ['company' => 'Nader-Abernathy',     'vat' => 'NI712345', 'phonenumber' => '+1-650-555-0101', 'website' => 'https://nader.example.com',    'city' => 'New York',     'country' => 'United States', 'active' => 1],
            ['company' => 'Mertz-Bergnaum',       'vat' => 'MB823456', 'phonenumber' => '+1-650-555-0102', 'website' => 'https://mertz.example.com',    'city' => 'Los Angeles',  'country' => 'United States', 'active' => 1],
            ['company' => 'Schroeder and Sons',   'vat' => 'SS934567', 'phonenumber' => '+44-20-555-0103', 'website' => 'https://schroeder.example.uk', 'city' => 'London',       'country' => 'United Kingdom','active' => 1],
            ['company' => 'Bayer Group',          'vat' => 'BG045678', 'phonenumber' => '+49-30-555-0104', 'website' => 'https://bayer.example.de',     'city' => 'Berlin',       'country' => 'Germany',       'active' => 1],
            ['company' => 'Halvorson LLC',        'vat' => 'HL156789', 'phonenumber' => '+1-650-555-0105', 'website' => 'https://halvorson.example.com','city' => 'Chicago',      'country' => 'United States', 'active' => 1],
            ['company' => 'Kub Group',            'vat' => 'KG267890', 'phonenumber' => '+1-650-555-0106', 'website' => 'https://kubgroup.example.com', 'city' => 'Houston',      'country' => 'United States', 'active' => 1],
            ['company' => 'Morar-Runte',          'vat' => 'MR378901', 'phonenumber' => '+33-1-555-0107',  'website' => 'https://morar.example.fr',     'city' => 'Paris',        'country' => 'France',        'active' => 1],
            ['company' => 'Torphy and Partners',  'vat' => 'TP489012', 'phonenumber' => '+1-650-555-0108', 'website' => 'https://torphy.example.com',   'city' => 'Phoenix',      'country' => 'United States', 'active' => 1],
            ['company' => 'Wunsch-Hyatt',         'vat' => 'WH590123', 'phonenumber' => '+1-650-555-0109', 'website' => 'https://wunsch.example.com',   'city' => 'Philadelphia', 'country' => 'United States', 'active' => 1],
            ['company' => 'Franecki Inc',         'vat' => 'FI601234', 'phonenumber' => '+61-2-555-0110',  'website' => 'https://franecki.example.au',  'city' => 'Sydney',       'country' => 'Australia',     'active' => 1],
            ['company' => 'Grimes-Fisher',        'vat' => 'GF712345', 'phonenumber' => '+1-650-555-0111', 'website' => 'https://grimes.example.com',   'city' => 'San Antonio',  'country' => 'United States', 'active' => 0],
            ['company' => 'Bogisich-Stehr',       'vat' => 'BS823456', 'phonenumber' => '+81-3-555-0112',  'website' => 'https://bogisich.example.jp',  'city' => 'Tokyo',        'country' => 'Japan',         'active' => 1],
        ];

        $contactFirstNames = ['James','Maria','Robert','Linda','Michael','Patricia','William','Barbara','David','Susan','Richard','Jessica'];
        $contactLastNames  = ['Smith','Johnson','Williams','Brown','Jones','Garcia','Miller','Davis','Wilson','Taylor','Anderson','Thomas'];
        $contactTitles     = ['CEO','CFO','CTO','Director','Manager','VP of Sales','Head of Operations','Founder'];

        foreach ($companies as $company) {
            $exists = DB::table('clients')->where('company', $company['company'])->exists();
            if (!$exists) {
                $clientId = DB::table('clients')->insertGetId([
                    'company'          => $company['company'],
                    'vat'              => $company['vat'],
                    'phonenumber'      => $company['phonenumber'],
                    'website'          => $company['website'],
                    'city'             => $company['city'],
                    'country'          => $company['country'],
                    'active'           => $company['active'],
                    'default_language' => 'english',
                    'address'          => rand(100, 999) . ' Business Park',
                    'state'            => 'CA',
                    'zip'              => '9' . rand(1000, 9999),
                    'created_at'       => now()->subDays(rand(10, 365)),
                    'updated_at'       => now(),
                ]);

                // Add primary contact
                $fn = $contactFirstNames[array_rand($contactFirstNames)];
                $ln = $contactLastNames[array_rand($contactLastNames)];
                $slug = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $company['company']));

                DB::table('contacts')->insertOrIgnore([
                    'client_id'   => $clientId,
                    'firstname'   => $fn,
                    'lastname'    => $ln,
                    'email'       => strtolower($fn . '.' . $ln . '@' . $slug . '.com'),
                    'phonenumber' => $company['phonenumber'],
                    'title'       => $contactTitles[array_rand($contactTitles)],
                    'is_primary'  => 1,
                    'active'      => 1,
                    'created_at'  => now()->subDays(rand(10, 365)),
                    'updated_at'  => now(),
                ]);
            }
        }

        // ── Lead Statuses & Sources ───────────────────────────────
        $statusCount = DB::table('lead_statuses')->count();
        if ($statusCount === 0) {
            DB::table('lead_statuses')->insert([
                ['name' => 'New',           'color' => '#6366f1', 'order_num' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Contacted',     'color' => '#0ea5e9', 'order_num' => 2, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Working',       'color' => '#f59e0b', 'order_num' => 3, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Qualified',     'color' => '#10b981', 'order_num' => 4, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Proposal Sent', 'color' => '#8b5cf6', 'order_num' => 5, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Customer',      'color' => '#22c55e', 'order_num' => 6, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Lost',          'color' => '#ef4444', 'order_num' => 7, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        $sourceCount = DB::table('lead_sources')->count();
        if ($sourceCount === 0) {
            DB::table('lead_sources')->insert([
                ['name' => 'Website',      'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Phone',        'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Email',        'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Referral',     'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Social Media', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Other',        'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        $statuses = DB::table('lead_statuses')->pluck('id')->toArray();
        $sources  = DB::table('lead_sources')->pluck('id')->toArray();

        // ── Leads ─────────────────────────────────────────────────
        $currentLeadCount = DB::table('leads')->count();
        if ($currentLeadCount < 20) {
            $leadPeople = [
                ['name' => 'Alice Wonderland',    'company' => 'Wonder Solutions'],
                ['name' => 'Bob Hoffman',         'company' => 'Hoffman Digital'],
                ['name' => 'Charlie Keast',       'company' => 'Keast & Co'],
                ['name' => 'Diana Prince',        'company' => 'Prince Consulting'],
                ['name' => 'Edward Norton',       'company' => 'Norton Tech'],
                ['name' => 'Fiona Green',         'company' => 'Green Media'],
                ['name' => 'George Michaels',     'company' => 'Michaels Group'],
                ['name' => 'Hannah Montana',      'company' => 'Montana Designs'],
                ['name' => 'Ivan Drago',          'company' => 'Drago Enterprises'],
                ['name' => 'Julia Roberts',       'company' => 'Roberts Agency'],
                ['name' => 'Kevin Hart',          'company' => 'Hart Productions'],
                ['name' => 'Laura Palmer',        'company' => 'Palmer Logistics'],
                ['name' => 'Matt Damon',          'company' => 'Damon Creative'],
                ['name' => 'Nancy Drew',          'company' => 'Drew Investigations'],
                ['name' => 'Oscar Wilde',         'company' => 'Wilde Arts'],
                ['name' => 'Paula Abdul',         'company' => 'Abdul Entertainment'],
                ['name' => 'Quinn Hughes',        'company' => 'Hughes Partners'],
                ['name' => 'Rachel Green',        'company' => 'Green Interiors'],
                ['name' => 'Steve Rogers',        'company' => 'Rogers Defense'],
                ['name' => 'Tina Turner',         'company' => 'Turner Music'],
                ['name' => 'Uma Thurman',         'company' => 'Thurman Productions'],
                ['name' => 'Victor Hugo',         'company' => 'Hugo Publishing'],
                ['name' => 'Wendy Williams',      'company' => 'Williams Media'],
                ['name' => 'Xander Harris',       'company' => 'Harris Security'],
                ['name' => 'Yara Greyjoy',        'company' => 'Greyjoy Maritime'],
                ['name' => 'Zara Phillips',       'company' => 'Phillips Fashion'],
                ['name' => 'Aaron Rodgers',       'company' => 'Rodgers Sports Co'],
                ['name' => 'Beth Dutton',         'company' => 'Dutton Ranch LLC'],
                ['name' => 'Carl Sagan',          'company' => 'Sagan Research'],
                ['name' => 'Diana Ross',          'company' => 'Ross Records'],
                ['name' => 'Elon Musk',           'company' => 'Musk Ventures'],
                ['name' => 'Frida Kahlo',         'company' => 'Kahlo Studios'],
                ['name' => 'Gordon Ramsay',       'company' => 'Ramsay Restaurants'],
                ['name' => 'Helena Bonham',       'company' => 'Bonham Theatre'],
                ['name' => 'Ian Fleming',         'company' => 'Fleming Spy Tech'],
                ['name' => 'Jane Austen',         'company' => 'Austen Literary'],
                ['name' => 'Kurt Cobain',         'company' => 'Cobain Music'],
                ['name' => 'Lana Del Rey',        'company' => 'Del Rey Branding'],
                ['name' => 'Mike Tyson',          'company' => 'Tyson Boxing'],
                ['name' => 'Nicki Minaj',         'company' => 'Minaj Entertainment'],
                ['name' => 'Oprah Winfrey',       'company' => 'Winfrey Media'],
                ['name' => 'Patrick Star',        'company' => 'Star Consulting'],
                ['name' => 'Quinn Elizabeth',     'company' => 'Elizabeth Royals'],
                ['name' => 'Ron Swanson',         'company' => 'Swanson Wood Works'],
                ['name' => 'Sarah Connor',        'company' => 'Connor Systems'],
                ['name' => 'Tom Hanks',           'company' => 'Hanks Productions'],
                ['name' => 'Uma Parvati',         'company' => 'Parvati Wellness'],
                ['name' => 'Viola Davis',         'company' => 'Davis Acting Studio'],
                ['name' => 'Walter White',        'company' => 'White Chemistry'],
                ['name' => 'Xena Warrior',        'company' => 'Warrior Fitness'],
            ];

            $cities = ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'Philadelphia', 'San Antonio', 'San Diego', 'Dallas', 'San Jose'];

            foreach ($leadPeople as $idx => $lead) {
                $slug = strtolower(preg_replace('/[^A-Za-z0-9]/', '.', $lead['name']));
                DB::table('leads')->insert([
                    'name'        => $lead['name'],
                    'company'     => $lead['company'],
                    'email'       => $slug . '@leads.example.com',
                    'phonenumber' => '+1-555-' . str_pad($idx + 200, 4, '0', STR_PAD_LEFT),
                    'status_id'   => $statuses[array_rand($statuses)],
                    'source_id'   => $sources[array_rand($sources)],
                    'lead_value'  => rand(500, 50000),
                    'city'        => $cities[array_rand($cities)],
                    'country'     => 'United States',
                    'description' => 'Interested in our services. Reached out via ' . ['website', 'email', 'referral', 'phone'][rand(0, 3)] . '.',
                    'created_at'  => now()->subDays(rand(1, 120)),
                    'updated_at'  => now(),
                ]);
            }
        }

        // ── Invoices ──────────────────────────────────────────────
        $currentInvoiceCount = DB::table('invoices')->count();
        $clientIds = DB::table('clients')->pluck('id')->toArray();

        if ($currentInvoiceCount < 13 && !empty($clientIds)) {
            // Match Perfex demo: 1 Draft, 13 Not Sent→unpaid, 9 Unpaid, 3 Partially Paid, 0 Overdue, 2 Paid
            $invoiceRows = [
                ['status' => 'draft',          'subtotal' => 1050.00,  'daysAgo' => 3],
                ['status' => 'unpaid',         'subtotal' => 2500.00,  'daysAgo' => 5],
                ['status' => 'unpaid',         'subtotal' => 1200.00,  'daysAgo' => 8],
                ['status' => 'unpaid',         'subtotal' => 3800.00,  'daysAgo' => 12],
                ['status' => 'unpaid',         'subtotal' => 750.00,   'daysAgo' => 15],
                ['status' => 'unpaid',         'subtotal' => 4200.00,  'daysAgo' => 18],
                ['status' => 'unpaid',         'subtotal' => 900.00,   'daysAgo' => 22],
                ['status' => 'unpaid',         'subtotal' => 2100.00,  'daysAgo' => 25],
                ['status' => 'unpaid',         'subtotal' => 3500.00,  'daysAgo' => 28],
                ['status' => 'unpaid',         'subtotal' => 1800.00,  'daysAgo' => 30],
                ['status' => 'unpaid',         'subtotal' => 6700.00,  'daysAgo' => 35],
                ['status' => 'unpaid',         'subtotal' => 940.00,   'daysAgo' => 38],
                ['status' => 'unpaid',         'subtotal' => 2250.00,  'daysAgo' => 42],
                ['status' => 'unpaid',         'subtotal' => 3100.00,  'daysAgo' => 45],
                ['status' => 'partially_paid', 'subtotal' => 5000.00,  'daysAgo' => 20],
                ['status' => 'partially_paid', 'subtotal' => 2800.00,  'daysAgo' => 40],
                ['status' => 'partially_paid', 'subtotal' => 1600.00,  'daysAgo' => 55],
                ['status' => 'overdue',        'subtotal' => 4400.00,  'daysAgo' => 65],
                ['status' => 'paid',           'subtotal' => 6000.00,  'daysAgo' => 70],
                ['status' => 'paid',           'subtotal' => 7091.40,  'daysAgo' => 90],
            ];

            $existingMax = DB::table('invoices')->max('id') ?? 0;
            $projectIds  = DB::table('projects')->pluck('id')->toArray();
            $tagPool     = ['design', 'development', 'seo', 'marketing', 'support', 'hosting', 'consulting', 'urgent'];
            foreach ($invoiceRows as $idx => $inv) {
                $num  = $existingMax + $idx + 1;
                $date = Carbon::now()->subDays($inv['daysAgo']);
                $tax  = round($inv['subtotal'] * 0.1, 2);
                $randomTags = array_rand(array_flip($tagPool), rand(1, 3));
                $tagsStr = implode(',', is_array($randomTags) ? $randomTags : [$randomTags]);
                DB::table('invoices')->insert([
                    'client_id'        => $clientIds[array_rand($clientIds)],
                    'project_id'       => !empty($projectIds) && rand(0, 1) ? $projectIds[array_rand($projectIds)] : null,
                    'number'           => 'INV-' . str_pad($num, 5, '0', STR_PAD_LEFT),
                    'status'           => $inv['status'],
                    'date'             => $date->toDateString(),
                    'duedate'          => $date->copy()->addDays(30)->toDateString(),
                    'subtotal'         => $inv['subtotal'],
                    'tax'              => $tax,
                    'discount_percent' => 0,
                    'discount_val'     => 0,
                    'total'            => round($inv['subtotal'] + $tax, 2),
                    'notes'            => null,
                    'tags'             => $tagsStr,
                    'created_at'       => $date,
                    'updated_at'       => now(),
                ]);
            }
        }

        // ── Backfill project_id + tags for any existing invoices missing them ────
        $tagPool    = ['design', 'development', 'seo', 'marketing', 'support', 'hosting', 'consulting', 'urgent'];
        $projectIds = DB::table('projects')->pluck('id')->toArray();
        $invoicesWithoutTags = DB::table('invoices')->whereNull('tags')->get();
        foreach ($invoicesWithoutTags as $inv) {
            $randomTags = array_rand(array_flip($tagPool), rand(1, 3));
            $tagsStr = implode(',', is_array($randomTags) ? $randomTags : [$randomTags]);
            DB::table('invoices')->where('id', $inv->id)->update([
                'project_id' => !empty($projectIds) && rand(0, 1) ? $projectIds[array_rand($projectIds)] : null,
                'tags'       => $tagsStr,
            ]);
        }

        // ── Invoice Items ─────────────────────────────────────────
        $allInvoices = DB::table('invoices')->get();
        foreach ($allInvoices as $inv) {
            $hasItems = DB::table('invoice_items')->where('invoice_id', $inv->id)->exists();
            if (!$hasItems) {
                $serviceItems = [
                    ['description' => 'Web Development', 'long_description' => 'Frontend and backend development services'],
                    ['description' => 'UI/UX Design', 'long_description' => 'Wireframing, prototyping and Figma design'],
                    ['description' => 'SEO Optimization', 'long_description' => 'On-page and off-page optimization package'],
                    ['description' => 'Monthly Hosting', 'long_description' => 'Server management and backups'],
                    ['description' => 'Consulting', 'long_description' => 'Technical advisory and strategy sessions'],
                    ['description' => 'Logo Design', 'long_description' => 'Brand identity and logo creation'],
                    ['description' => 'Content Writing', 'long_description' => 'Blog posts and website copy'],
                ];
                $numItems = rand(1, 3);
                $shuffled = array_rand($serviceItems, $numItems);
                if (!is_array($shuffled)) $shuffled = [$shuffled];
                $subtotal = 0;
                foreach ($shuffled as $key) {
                    $qty  = rand(1, 5);
                    $rate = round($inv->subtotal / ($numItems * rand(1, 3)), 2);
                    $subtotal += $qty * $rate;
                    DB::table('invoice_items')->insert([
                        'invoice_id'       => $inv->id,
                        'description'      => $serviceItems[$key]['description'],
                        'long_description' => $serviceItems[$key]['long_description'],
                        'qty'              => $qty,
                        'rate'             => $rate,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }
            }
        }

        $userIds = DB::table('users')->pluck('id')->toArray();

        // ── Predefined Catalog Items ───────────────────────────────
        if (DB::table('predefined_items')->count() === 0) {
            DB::table('predefined_items')->insert([
                ['name' => 'WordPress Development', 'description' => 'Custom theme and plugin development', 'rate' => 120.00, 'tax_rate' => 10.00, 'unit' => 'Hour', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'SEO Optimization', 'description' => 'On-page and off-page SEO optimization package', 'rate' => 500.00, 'tax_rate' => 5.00, 'unit' => 'Month', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'UI/UX Design', 'description' => 'Figma design and prototyping', 'rate' => 90.00, 'tax_rate' => 10.00, 'unit' => 'Hour', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Monthly Hosting Support', 'description' => 'Server maintenance and backup services', 'rate' => 99.00, 'tax_rate' => 0.00, 'unit' => 'Month', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // ── Payments ─────────────────────────────────────────────
        $invoicesList = DB::table('invoices')->whereIn('status', ['paid', 'partially_paid'])->get();
        foreach ($invoicesList as $inv) {
            $alreadyHasPayment = DB::table('payments')->where('invoice_id', $inv->id)->exists();
            if (!$alreadyHasPayment) {
                $payAmount = $inv->status === 'paid' ? $inv->total : round($inv->total / 2, 2);
                DB::table('payments')->insert([
                    'invoice_id' => $inv->id,
                    'amount' => $payAmount,
                    'paymentmode' => ['Bank Transfer', 'Stripe', 'PayPal', 'Cash'][rand(0, 3)],
                    'date' => $inv->date,
                    'transactionid' => 'TXN-' . strtoupper(bin2hex(random_bytes(4))),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // ── Credit Notes ──────────────────────────────────────────
        if (DB::table('credit_notes')->count() === 0 && !empty($clientIds)) {
            for ($i = 1; $i <= 3; $i++) {
                DB::table('credit_notes')->insert([
                    'number' => 'CN-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'client_id' => $clientIds[array_rand($clientIds)],
                    'amount' => rand(100, 1500),
                    'date' => now()->subDays(rand(5, 30))->toDateString(),
                    'status' => ['Open', 'Closed'][rand(0, 1)],
                    'reference' => 'REF-' . rand(1000, 9999),
                    'admin_note' => 'Demo credit note.',
                    'terms' => 'Standard credit terms apply.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // ── Subscriptions ─────────────────────────────────────────
        if (DB::table('subscriptions')->count() === 0 && !empty($clientIds)) {
            $subPlans = ['Basic Support', 'Premium Support', 'Enterprise Cloud Hosting', 'Monthly SEO Package'];
            foreach ($subPlans as $planName) {
                DB::table('subscriptions')->insert([
                    'name' => $planName,
                    'client_id' => $clientIds[array_rand($clientIds)],
                    'amount' => rand(49, 999),
                    'billing_period' => ['monthly', 'yearly'][rand(0, 1)],
                    'status' => ['active', 'inactive'][rand(0, 1)],
                    'start_date' => now()->subDays(rand(10, 100))->toDateString(),
                    'stripe_plan' => 'price_' . bin2hex(random_bytes(6)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // ── Projects ─────────────────────────────────────────────
        if (DB::table('projects')->count() === 0 && !empty($clientIds)) {
            $projectsList = [
                ['name' => 'CRM Expansion', 'billing_type' => 'Fixed Rate', 'budget' => 15000.00, 'status' => 'In Progress'],
                ['name' => 'Mobile App Design', 'billing_type' => 'Project Hours', 'budget' => 8000.00, 'status' => 'In Progress'],
                ['name' => 'Website Redesign', 'billing_type' => 'Fixed Rate', 'budget' => 5000.00, 'status' => 'Finished'],
                ['name' => 'ERP Integration', 'billing_type' => 'Fixed Rate', 'budget' => 25000.00, 'status' => 'Not Started'],
            ];
            foreach ($projectsList as $idx => $p) {
                $projId = DB::table('projects')->insertGetId([
                    'name' => $p['name'],
                    'description' => 'This is a demo project to build ' . strtolower($p['name']) . '.',
                    'client_id' => $clientIds[array_rand($clientIds)],
                    'billing_type' => $p['billing_type'],
                    'budget' => $p['budget'],
                    'start_date' => now()->subDays(rand(15, 60))->toDateString(),
                    'deadline' => now()->addDays(rand(30, 90))->toDateString(),
                    'status' => $p['status'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Assign random project members
                $memberCount = rand(2, 4);
                $randomMembers = array_rand($userIds, min($memberCount, count($userIds)));
                if (!is_array($randomMembers)) {
                    $randomMembers = [$randomMembers];
                }
                foreach ($randomMembers as $mIdx) {
                    DB::table('project_members')->insert([
                        'project_id' => $projId,
                        'user_id' => $userIds[$mIdx],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // ── Tasks ────────────────────────────────────────────────
        if (DB::table('tasks')->count() === 0 && !empty($userIds)) {
            $tasksList = [
                ['name' => 'Define Database Schema', 'priority' => 'High', 'status' => 'Complete'],
                ['name' => 'Create Frontend Views', 'priority' => 'Medium', 'status' => 'In Progress'],
                ['name' => 'Write Backend APIs', 'priority' => 'Urgent', 'status' => 'In Progress'],
                ['name' => 'Setup CI/CD Pipeline', 'priority' => 'Low', 'status' => 'Not Started'],
                ['name' => 'Perform UAT Testing', 'priority' => 'Medium', 'status' => 'Testing'],
                ['name' => 'Verify Security Settings', 'priority' => 'High', 'status' => 'Awaiting Feedback'],
            ];
            foreach ($tasksList as $idx => $t) {
                DB::table('tasks')->insert([
                    'name' => $t['name'],
                    'description' => 'Task details for ' . strtolower($t['name']) . '.',
                    'priority' => $t['priority'],
                    'status' => $t['status'],
                    'start_date' => now()->subDays(rand(2, 10))->toDateString(),
                    'due_date' => now()->addDays(rand(2, 20))->toDateString(),
                    'assigned_to' => $userIds[array_rand($userIds)],
                    'related_to_type' => 'project',
                    'related_to_id' => DB::table('projects')->first()?->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // ── Expenses ─────────────────────────────────────────────
        if (DB::table('expense_categories')->count() === 0) {
            DB::table('expense_categories')->insert([
                ['name' => 'Hosting & Cloud Servers', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Software Licenses', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Office Supplies', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Travel & Commute', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
        $expenseCategories = DB::table('expense_categories')->pluck('id')->toArray();
        if (DB::table('expenses')->count() === 0 && !empty($expenseCategories) && !empty($clientIds)) {
            $expensesList = [
                ['name' => 'AWS Invoice May 2026', 'amount' => 450.00, 'reference' => 'AWS-998822'],
                ['name' => 'GitHub Enterprise Licenses', 'amount' => 120.00, 'reference' => 'GH-7722'],
                ['name' => 'Figma Professional Team Plan', 'amount' => 75.00, 'reference' => 'FIG-2331'],
                ['name' => 'Office Rent', 'amount' => 2500.00, 'reference' => 'RENT-MAY'],
            ];
            foreach ($expensesList as $e) {
                DB::table('expenses')->insert([
                    'name' => $e['name'],
                    'note' => 'Billed for project resources.',
                    'amount' => $e['amount'],
                    'date' => now()->subDays(rand(5, 25))->toDateString(),
                    'category_id' => $expenseCategories[array_rand($expenseCategories)],
                    'client_id' => $clientIds[array_rand($clientIds)],
                    'reference' => $e['reference'],
                    'payment_mode' => 'Credit Card',
                    'status' => ['unbilled', 'billed'][rand(0, 1)],
                    'billable' => rand(0, 1),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // ── Contracts ────────────────────────────────────────────
        if (DB::table('contract_types')->count() === 0) {
            DB::table('contract_types')->insert([
                ['name' => 'NDA Agreement', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Service Level Agreement (SLA)', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Software License Agreement', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
        $contractTypes = DB::table('contract_types')->pluck('id')->toArray();
        if (DB::table('contracts')->count() === 0 && !empty($contractTypes) && !empty($clientIds)) {
            $contractsList = [
                ['subject' => 'Mutual Non-Disclosure Agreement', 'value' => 0.00, 'status' => 'Active', 'signed' => true],
                ['subject' => 'Monthly Retainer SLA', 'value' => 5000.00, 'status' => 'Active', 'signed' => true],
                ['subject' => 'Product Licensing Agreement', 'value' => 12000.00, 'status' => 'Expired', 'signed' => false],
            ];
            foreach ($contractsList as $c) {
                DB::table('contracts')->insert([
                    'subject' => $c['subject'],
                    'client_id' => $clientIds[array_rand($clientIds)],
                    'contract_type_id' => $contractTypes[array_rand($contractTypes)],
                    'value' => $c['value'],
                    'start_date' => now()->subDays(100)->toDateString(),
                    'end_date' => now()->addDays(rand(10, 100))->toDateString(),
                    'status' => $c['status'],
                    'description' => 'Demo contract terms.',
                    'signed' => $c['signed'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // ── Estimates ────────────────────────────────────────────
        if (DB::table('estimates')->count() === 0 && !empty($clientIds)) {
            for ($i = 1; $i <= 3; $i++) {
                $sub = rand(500, 3000);
                $tax = round($sub * 0.1, 2);
                $estId = DB::table('estimates')->insertGetId([
                    'number' => 'EST-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'client_id' => $clientIds[array_rand($clientIds)],
                    'date' => now()->subDays(rand(1, 15))->toDateString(),
                    'expiry_date' => now()->addDays(15)->toDateString(),
                    'status' => ['Draft', 'Sent', 'Accepted'][rand(0, 2)],
                    'subtotal' => $sub,
                    'discount' => 0.00,
                    'discount_type' => 'percent',
                    'tax' => $tax,
                    'total' => $sub + $tax,
                    'currency' => 'USD',
                    'note' => 'Thank you for your interest.',
                    'terms' => 'Valid for 15 days.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('estimate_items')->insert([
                    'estimate_id' => $estId,
                    'description' => 'Consulting Services',
                    'long_description' => 'Professional architecture review and plan.',
                    'qty' => 1.00,
                    'unit' => 'Service',
                    'rate' => $sub,
                    'tax' => 10.00,
                    'total' => $sub,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // ── Announcements & Goals ─────────────────────────────────
        if (DB::table('announcements')->count() === 0) {
            DB::table('announcements')->insert([
                ['subject' => 'Welcome to Perfex CRM!', 'message' => 'Please complete your setup profile and add client accounts.', 'show_to_staff' => true, 'show_to_clients' => true, 'created_at' => now(), 'updated_at' => now()],
                ['subject' => 'Quarterly Planning Session', 'message' => 'The quarterly planning meeting is scheduled for next Friday at 10 AM.', 'show_to_staff' => true, 'show_to_clients' => false, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if (DB::table('goals')->count() === 0) {
            DB::table('goals')->insert([
                ['subject' => 'Monthly Sales Target', 'goal_type' => 'invoice_amount', 'start_date' => now()->startOfMonth()->toDateString(), 'end_date' => now()->endOfMonth()->toDateString(), 'target_value' => 50000.00, 'current_value' => 14182.80, 'created_at' => now(), 'updated_at' => now()],
                ['subject' => 'Lead Acquisition Goal', 'goal_type' => 'lead_conversion', 'start_date' => now()->startOfMonth()->toDateString(), 'end_date' => now()->endOfMonth()->toDateString(), 'target_value' => 20.00, 'current_value' => 8.00, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // ── Ticket Departments & Tickets ──────────────────────────
        if (DB::table('ticket_departments')->count() === 0) {
            DB::table('ticket_departments')->insert([
                ['name' => 'Technical Support', 'description' => 'Server, API, and technical glitches support', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Billing & Finance', 'description' => 'Invoices, payments, and billing inquiries', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
        $departments = DB::table('ticket_departments')->pluck('id')->toArray();
        if (DB::table('tickets')->count() === 0 && !empty($departments) && !empty($clientIds) && !empty($userIds)) {
            $ticketsList = [
                ['subject' => 'Error loading dashboard metrics', 'priority' => 'High', 'status' => 'Open', 'message' => 'Getting a 500 error when loading Dashboard page.'],
                ['subject' => 'Custom plugin integration questions', 'priority' => 'Medium', 'status' => 'Answered', 'message' => 'How can I integrate the payment gateway API?'],
            ];
            foreach ($ticketsList as $t) {
                $ticketId = DB::table('tickets')->insertGetId([
                    'subject' => $t['subject'],
                    'client_id' => $clientIds[array_rand($clientIds)],
                    'priority' => $t['priority'],
                    'status' => $t['status'],
                    'department_id' => $departments[array_rand($departments)],
                    'message' => $t['message'],
                    'assigned_to' => $userIds[array_rand($userIds)],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($t['status'] === 'Answered') {
                    DB::table('ticket_replies')->insert([
                        'ticket_id' => $ticketId,
                        'message' => 'Please check our API documentation under developer resources.',
                        'user_id' => $userIds[array_rand($userIds)],
                        'is_admin_reply' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // ── Estimate Requests ─────────────────────────────────────
        if (DB::table('estimate_requests')->count() === 0) {
            DB::table('estimate_requests')->insert([
                ['name' => 'Alice Green', 'email' => 'alice@green.example', 'message' => 'Requesting an estimate for a custom Magento integration website.', 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'David Stone', 'email' => 'david@stone.example', 'message' => 'Need a consultation quote for SEO Audit.', 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // ── Estimate Request Forms ────────────────────────────────
        if (DB::table('estimate_request_forms')->count() === 0) {
            DB::table('estimate_request_forms')->insert([
                ['name' => 'Website Development Quote', 'email' => 'info@website.com', 'tags' => 'web,development', 'assigned_to' => 2, 'status' => 'active', 'language' => 'English', 'recaptcha_enabled' => true, 'submit_btn_text' => 'Submit', 'submit_btn_bg_color' => '#84c529', 'submission_action' => 'message', 'submission_message' => 'Thank you for your inquiry. Our team will contact you within 24 hours.', 'submission_redirect_url' => null, 'notify_enabled' => true, 'notify_type' => 'specific', 'notify_staff_ids' => '1,2', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Consulting Inquiry', 'email' => 'hello@consult.io', 'tags' => 'consulting', 'assigned_to' => 1, 'status' => 'processing', 'language' => 'English', 'recaptcha_enabled' => false, 'submit_btn_text' => 'Send Request', 'submit_btn_bg_color' => '#0b6eff', 'submission_action' => 'redirect', 'submission_message' => null, 'submission_redirect_url' => 'https://consult.io/thank-you', 'notify_enabled' => true, 'notify_type' => 'responsible', 'notify_staff_ids' => null, 'created_at' => now()->subDay(), 'updated_at' => now()->subDay()],
                ['name' => 'SEO Audit Request', 'email' => 'seo@marketing.co', 'tags' => 'seo,marketing', 'assigned_to' => 3, 'status' => 'processing', 'language' => 'Spanish', 'recaptcha_enabled' => true, 'submit_btn_text' => 'Submit', 'submit_btn_bg_color' => '#84c529', 'submission_action' => 'message', 'submission_message' => 'Gracias por su solicitud. Le responderemos pronto.', 'submission_redirect_url' => null, 'notify_enabled' => false, 'notify_type' => 'specific', 'notify_staff_ids' => null, 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)],
                ['name' => 'Partnership Proposal', 'email' => 'partner@business.com', 'tags' => 'partnership', 'assigned_to' => null, 'status' => 'inactive', 'language' => 'English', 'recaptcha_enabled' => false, 'submit_btn_text' => 'Submit', 'submit_btn_bg_color' => '#84c529', 'submission_action' => 'message', 'submission_message' => 'Thank you for your interest.', 'submission_redirect_url' => null, 'notify_enabled' => false, 'notify_type' => 'specific', 'notify_staff_ids' => null, 'created_at' => now()->subDays(5), 'updated_at' => now()->subDays(5)],
            ]);
        }

        // ── Knowledge Base ────────────────────────────────────────
        if (DB::table('kb_categories')->count() === 0) {
            DB::table('kb_categories')->insert([
                ['name' => 'Sales', 'slug' => 'sales', 'color' => '#10b981', 'description' => 'Sales-related articles and guides', 'order_num' => 1, 'disabled' => false, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Info', 'slug' => 'info', 'color' => '#3b82f6', 'description' => 'General information and FAQs', 'order_num' => 2, 'disabled' => false, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Support', 'slug' => 'support', 'color' => '#8b5cf6', 'description' => 'Technical support documentation', 'order_num' => 3, 'disabled' => false, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Development', 'slug' => 'development', 'color' => '#f59e0b', 'description' => 'Developer guides and API docs', 'order_num' => 4, 'disabled' => false, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Security', 'slug' => 'security', 'color' => '#ef4444', 'description' => 'Security best practices', 'order_num' => 5, 'disabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
        $kbCats = DB::table('kb_categories')->where('disabled', false)->pluck('id', 'name')->toArray();
        if (DB::table('kb_articles')->count() === 0 && !empty($kbCats)) {
            $articles = [
                ['title' => 'He unfolded the paper.', 'content' => 'He unfolded the paper and studied it for a moment. The information contained within was both surprising and expected at the same time. He had known for a while that something was wrong, but he had hoped against hope that he was mistaken. The truth was undeniable though, and he had to accept it.', 'category_id' => $kbCats['Sales'] ?? null, 'status' => 'published', 'views_count' => 25, 'created_at' => now(), 'updated_at' => now()],
                ['title' => 'Alice, every now and then, and.', 'content' => 'Alice, every now and then, and found in it a very good height indeed!) said the Hatter. This piece of rudeness was more than Alice could not help bursting out of sight before the trial\'s over! Though they were nice grand words to say.', 'category_id' => $kbCats['Sales'] ?? null, 'status' => 'published', 'views_count' => 18, 'created_at' => now(), 'updated_at' => now()],
                ['title' => 'For instance, if you could keep.', 'content' => 'For instance, if you could keep it in the lock, and to her feet in a very little use without my shoulders. Oh, how I wish I could show you our cat Dinah: I wish you were down here with me! There are no mice in the air.', 'category_id' => $kbCats['Sales'] ?? null, 'status' => 'published', 'views_count' => 32, 'created_at' => now(), 'updated_at' => now()],
                ['title' => 'Alice was not easy to know.', 'content' => 'Alice was not easy to know when the Rabbit came near her, she began thinking over other children she knew that were of the same size: to do so. "What a curious dream!\' said Alice, (she had grown to her chin in salt water. Her first idea was that she had never before seen a rabbit with either a waistcoat-pocket, or a watch to take out of sight before the trial\'s over!".', 'category_id' => $kbCats['Sales'] ?? null, 'status' => 'published', 'views_count' => 14, 'created_at' => now(), 'updated_at' => now()],
                ['title' => 'There ought to eat her.', 'content' => 'There ought to eat her up in a very fine day!\' said a timid and tremulous sound. "That\'s different from what I used to call him Softly, in a low voice, "Why the fact is, you see, Miss, we\'re doing our best, as the door of the beautiful garden, among the distant green leaves the Mock Turtle yawned and shut his eyes. \'Tell us a story.\' said the March Hare. "Then it doesn\'t matter much,\' thought Alice, \'and if it makes me grow larger, I can reach the key; and if I only wish people knew that: then they wouldn\'t be so stingy about it, you know--".', 'category_id' => $kbCats['Sales'] ?? null, 'status' => 'published', 'views_count' => 22, 'created_at' => now(), 'updated_at' => now()],
                ['title' => 'I\'ll get into her.', 'content' => 'I\'ll get into her eyes, and felt quite strange at first; but she ran off at once without waiting for the first question. "What happened to you? Tell us all about it!" Alice felt that she was saying, and the others looked at Alice, as she could not think of anything to say, she simply bowed, and took the place where it had fallen into it: there were no tears. \'If you\'re going to be a person of authority among them, called out, \'Sit down, all of you, and don\'t speak a word till I\'ve finished.\' But they all looked so good that were of the hall: in fact she was trying to fix on one, the cook took the cauldron of soup off the fire, and after a fashion, and then treading on her toes when they met in the world you fly, Like a tea-tray in the sky. Here the Dormouse slowly opened his eyes. \'I wasn\'t asleep,\' he said in a sulky tone; \'Seven jogged my elbow.\'', 'category_id' => $kbCats['Sales'] ?? null, 'status' => 'published', 'views_count' => 9, 'created_at' => now(), 'updated_at' => now()],
                ['title' => 'Alice felt a violent shake at the stick.', 'content' => 'Alice felt a violent shake at the stick, running a very long silence, broken only by an occasional exclamation of "Hjckrrh!" from the Gryphon, and the constant heavy sobbing of the Mock Turtle. "What is his sorrow?" she asked the Gryphon, and the constant heavy sobbing of the Mock Turtle. "Hold your tongue!" added the Hatter, and he went on in a low voice, "Why the fact is, you see, Miss, we\'re doing our best, as the large birds complained that they could not remember ever having seen in her brother\'s Latin Grammar, A mouse--of a mouse--to a mouse--a mouse--O mouse!\') The Mouse looked at her, and the small ones choked and had to ask help of the lefthand bit. * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *', 'category_id' => $kbCats['Info'] ?? null, 'status' => 'published', 'views_count' => 45, 'created_at' => now(), 'updated_at' => now()],
                ['title' => 'Dodo in an encouraging opening for a.', 'content' => 'Dodo in an encouraging opening for a conversation. Alice replied, rather shyly, \'I--I hardly know, Sir, just at first, perhaps,\' said the Cat: \'we\'re all mad here. I\'m mad. You\'re mad.\' \'How do you know that you\'re mad?\' said Alice. \'To begin with,\' said the Cat, \'or you wouldn\'t have come here.\' Alice didn\'t think that proved it at all; however, she again heard a little shriek and a fall, and a crash of broken glass, from which she concluded that it was indeed: she was now about a thousand times as large as the rest of the conversation. Alice felt that it would be quite as safe to stay with it as to size, and the others looked at it, busily squaring the slate with large diamonds on it, and on it except a tiny little thing!\' It did so indeed, and much sooner than she had expected: before she had found the fan and the small ones choked and had to stop and untwist it. After a while she remembered that she had never seen or heard of one,\' said Alice, in a great deal to ME,\' said Alice hastily; \'but I\'m not used to it as well as she could, for her to carry it back. "If anybody can\'t help it," she said to herself, (not in a low, weak voice. "Now, I give you fair warning," shouted the Queen, and Alice, were in custody and under sentence of execution.\' \'What for?\' said the Dodo. Then they all crowded round her once more, while the rest of the key was too small, but at the Lizard as she could not answer either question, it didn\'t much matter which way you can;--but I must be off, and she swam about, trying to touch her. Poor little Lizard, Bill, was in the pool, \'and she sits purring so nicely by the whiting,\' said the Mock Turtle, \'they--you\'ve seen them, of course?\' \'Yes,\' said Alice, \'it\'s very interesting. I never was so full of soup. "There\'s certainly too much pepper in that case I can reach the key; and if it makes me grow larger, I can creep under the table: she opened it, and on it were white, but the cook was leaning over the list, feeling very glad that it led into the air. "--as far out to sea as you can--" \'Swim after them! Swim after them!\' screamed the Gryphon. \'It\'s all her fancy, that: they never executes nobody, you know. So you see, Miss, we\'re doing our best, as the soldiers shouted in the back. However, it was over at last: and the baby violently up and down looking for them, and he\'s treading on her toes when they saw Alice coming. \'There\'s plenty of time!\' said the Dodo. Then they all crowded round her at the bottom of a book,\' thought Alice \'without pictures or conversation?\' So she was a little startled by seeing the Cheshire Cat sitting on a crimson velvet cushion; and, last of all the jurymen are back in a hurry: a large flower-pot that was linked into hers began to cry again. \'You ought to be ashamed of yourself for asking such a pleasant temper, and thought to yourself, If you can\'t be civil, you\'d better finish the story for yourself.\' \'No, please go on!\' cried the Gryphon. \'It\'s all about as curious as it went. So she began: \'O Mouse, do you know that you\'re mad?\' \'To begin with,\' said the March Hare. \'I didn\'t know that you\'re mad?\' \'To begin with,\' the Mock Turtle sighed deeply, and began, in a large dish, and set to work very diligently to write with a deep sigh, \'I was a long way back, and see after some executions I have to go near the house if it makes rather a handsome pig, I think.\' And she squeezed herself up closer to Alice\'s elbow, and the words \'DRINK ME,\' but nevertheless she uncorked it and put it into one of them hit her in the same size for ten minutes together!\' \'Don\'t grunt,\' said Alice; \'I may as well look and see what I get" is the same when I get it home?\' when it saw mine coming!\' \'How do you know about it, and very neatly and simply arranged; the only difficulty was, that you couldn\'t cut off a little timidly: \'would you like to be true): If she should push the matter worse. You MUST have meant mischief.  \'The question is,\' said Alice, always ready to talk about her and to hear the name \'W. RABBIT\' engraved upon it. She stretched herself up on to the Knave of Hearts, who only bowed and smiled in reply. \'Please would you tell me,\' said Alice, a good deal frightened by this time, as it was quite pleased to find any. And yet I wish you were or not?\' So they began solemnly dancing round and round the refreshments!\' But everything upon her face. \'Very,\' said the King, and the other unpleasant things, all because they WOULD put things into the book. However, she did not at all like the right way to hear the name again!\' \'I won\'t indeed!\' said the Pigeon had finished. \'As if it wasn\'t very civil of you to sit down without being invited,\' said the King said to herself, \'Which way? Which way?\', holding her hand on the end of the doors of the cupboards as she could not tell whether they were all writing very busily on slates. \'What are you doing out here? Run home this moment, and fetch me a good deal to come once a week: HE taught us Drawling, Stretching, and Fainting.', 'category_id' => $kbCats['Info'] ?? null, 'status' => 'published', 'views_count' => 17, 'created_at' => now(), 'updated_at' => now()],
            ];
            DB::table('kb_articles')->insert($articles);
        }

        $this->command->info('');
        $this->command->info('✅ Demo data seeded successfully!');
        $this->command->info('   Staff:         ' . DB::table('users')->count() . ' records');
        $this->command->info('   Clients:       ' . DB::table('clients')->count() . ' records');
        $this->command->info('   Contacts:      ' . DB::table('contacts')->count() . ' records');
        $this->command->info('   Leads:         ' . DB::table('leads')->count() . ' records');
        $this->command->info('   Invoices:      ' . DB::table('invoices')->count() . ' records');
        $this->command->info('   Projects:      ' . DB::table('projects')->count() . ' records');
        $this->command->info('   Tasks:         ' . DB::table('tasks')->count() . ' records');
        $this->command->info('   Expenses:      ' . DB::table('expenses')->count() . ' records');
        $this->command->info('   Contracts:     ' . DB::table('contracts')->count() . ' records');
        $this->command->info('   Tickets:       ' . DB::table('tickets')->count() . ' records');
        $this->command->info('   KB Articles:     ' . DB::table('kb_articles')->count() . ' records');
        $this->command->info('   KB Categories:  ' . DB::table('kb_categories')->count() . ' records');
    }
}
