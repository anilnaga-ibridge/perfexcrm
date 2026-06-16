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

        // ── Knowledge Base ────────────────────────────────────────
        if (DB::table('kb_categories')->count() === 0) {
            DB::table('kb_categories')->insert([
                ['name' => 'General Inquiries', 'slug' => 'general', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'System Troubleshooting', 'slug' => 'troubleshooting', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
        $kbCats = DB::table('kb_categories')->pluck('id')->toArray();
        if (DB::table('kb_articles')->count() === 0 && !empty($kbCats)) {
            DB::table('kb_articles')->insert([
                ['title' => 'How to setup your CRM portal credentials', 'content' => 'Go to Setup > Staff and click edit to configure your password and active permissions.', 'category_id' => $kbCats[0], 'status' => 'published', 'views_count' => 45, 'created_at' => now(), 'updated_at' => now()],
                ['title' => 'Troubleshooting API 500 error codes', 'content' => 'Verify your MySQL server connection settings in your local environment file.', 'category_id' => $kbCats[1], 'status' => 'published', 'views_count' => 110, 'created_at' => now(), 'updated_at' => now()],
            ]);
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
        $this->command->info('   KB Articles:   ' . DB::table('kb_articles')->count() . ' records');
    }
}
