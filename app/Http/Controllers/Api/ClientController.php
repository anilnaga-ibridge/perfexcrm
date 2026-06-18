<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Contact;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    /**
     * Display a listing of clients.
     */
    public function index(Request $request)
    {
        $query = Client::withCount('contacts');

        // Search filter
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('company', 'like', "%{$search}%")
                  ->orWhere('vat', 'like', "%{$search}%")
                  ->orWhere('phonenumber', 'like', "%{$search}%")
                  ->orWhere('website', 'like', "%{$search}%");
            });
        }

        // Active filter
        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        // Pagination
        $perPage = $request->input('per_page', 10);
        $clients = $query->orderBy('company', 'asc')->paginate($perPage);

        // Summaries
        $totalCustomers = Client::count();
        $activeCustomers = Client::where('active', true)->count();
        $inactiveCustomers = Client::where('active', false)->count();
        $activeContacts = Contact::where('active', true)->count();
        $inactiveContacts = Contact::where('active', false)->count();

        return response()->json([
            'clients' => $clients,
            'summary' => [
                'total_customers' => $totalCustomers,
                'active_customers' => $activeCustomers,
                'inactive_customers' => $inactiveCustomers,
                'active_contacts' => $activeContacts,
                'inactive_contacts' => $inactiveContacts,
                'contacts_logged_in_today' => 0
            ]
        ]);
    }

    /**
     * Store a newly created client in database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company' => 'required|string|max:255',
            'vat' => 'nullable|string|max:255',
            'phonenumber' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'zip' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'default_language' => 'nullable|string|max:255',
            'groups' => 'nullable|string|max:255',
            'currency' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            
            // Billing
            'billing_street' => 'nullable|string|max:255',
            'billing_city' => 'nullable|string|max:255',
            'billing_state' => 'nullable|string|max:255',
            'billing_zip' => 'nullable|string|max:255',
            'billing_country' => 'nullable|string|max:255',
            
            // Shipping
            'shipping_street' => 'nullable|string|max:255',
            'shipping_city' => 'nullable|string|max:255',
            'shipping_state' => 'nullable|string|max:255',
            'shipping_zip' => 'nullable|string|max:255',
            'shipping_country' => 'nullable|string|max:255',
            
            // Primary Contact optional creation
            'contact_firstname' => 'nullable|required_with:contact_email|string|max:255',
            'contact_lastname' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|unique:contacts,email|max:255',
            'contact_phone' => 'nullable|string|max:255',
            'contact_title' => 'nullable|string|max:255',
            'contact_password' => 'nullable|string|min:6',
        ]);

        try {
            DB::beginTransaction();

            $client = Client::create([
                'company' => $validated['company'],
                'vat' => $validated['vat'] ?? null,
                'phonenumber' => $validated['phonenumber'] ?? null,
                'website' => $validated['website'] ?? null,
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'] ?? null,
                'state' => $validated['state'] ?? null,
                'zip' => $validated['zip'] ?? null,
                'country' => $validated['country'] ?? null,
                'default_language' => $validated['default_language'] ?? 'english',
                'groups' => $validated['groups'] ?? null,
                'currency' => $validated['currency'] ?? null,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                
                // Billing
                'billing_street' => $validated['billing_street'] ?? null,
                'billing_city' => $validated['billing_city'] ?? null,
                'billing_state' => $validated['billing_state'] ?? null,
                'billing_zip' => $validated['billing_zip'] ?? null,
                'billing_country' => $validated['billing_country'] ?? null,
                
                // Shipping
                'shipping_street' => $validated['shipping_street'] ?? null,
                'shipping_city' => $validated['shipping_city'] ?? null,
                'shipping_state' => $validated['shipping_state'] ?? null,
                'shipping_zip' => $validated['shipping_zip'] ?? null,
                'shipping_country' => $validated['shipping_country'] ?? null,
            ]);

            // Add contact if provided
            if (!empty($validated['contact_email'])) {
                Contact::create([
                    'client_id' => $client->id,
                    'firstname' => $validated['contact_firstname'],
                    'lastname' => $validated['contact_lastname'] ?? null,
                    'email' => $validated['contact_email'],
                    'phonenumber' => $validated['contact_phone'] ?? null,
                    'title' => $validated['contact_title'] ?? null,
                    'password' => !empty($validated['contact_password']) ? Hash::make($validated['contact_password']) : null,
                    'is_primary' => true,
                    'active' => true,
                ]);
            }

            DB::commit();
            return response()->json($client->load('contacts'), 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create customer: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified client.
     */
    public function show($id)
    {
        $client = Client::with('contacts')->find($id);

        if (!$client) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        return response()->json($client);
    }

    /**
     * Update the specified client in database.
     */
    public function update(Request $request, $id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $validated = $request->validate([
            'company' => 'required|string|max:255',
            'vat' => 'nullable|string|max:255',
            'phonenumber' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'zip' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'default_language' => 'nullable|string|max:255',
            'groups' => 'nullable|string|max:255',
            'currency' => 'nullable|string|max:255',
            'active' => 'boolean',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            
            // Billing
            'billing_street' => 'nullable|string|max:255',
            'billing_city' => 'nullable|string|max:255',
            'billing_state' => 'nullable|string|max:255',
            'billing_zip' => 'nullable|string|max:255',
            'billing_country' => 'nullable|string|max:255',
            
            // Shipping
            'shipping_street' => 'nullable|string|max:255',
            'shipping_city' => 'nullable|string|max:255',
            'shipping_state' => 'nullable|string|max:255',
            'shipping_zip' => 'nullable|string|max:255',
            'shipping_country' => 'nullable|string|max:255',
        ]);

        $client->update($validated);

        return response()->json($client);
    }

    /**
     * Remove the specified client from database.
     */
    public function destroy($id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $client->delete();

        return response()->json(['message' => 'Customer deleted successfully']);
    }

    /**
     * Toggle active status of customer.
     */
    public function updateStatus(Request $request, $id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $validated = $request->validate([
            'active' => 'required|boolean'
        ]);

        $client->update([
            'active' => $validated['active']
        ]);

        return response()->json([
            'message' => 'Customer status updated successfully',
            'client' => $client
        ]);
    }
}
