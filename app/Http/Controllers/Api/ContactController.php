<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contact;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ContactController extends Controller
{
    /**
     * Display a listing of contacts.
     */
    public function index(Request $request)
    {
        $query = Contact::with('client');

        // Search filter
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phonenumber', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhereHas('client', function($cq) use ($search) {
                      $cq->where('company', 'like', "%{$search}%");
                  });
            });
        }

        // Active filter
        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        // Pagination
        $perPage = $request->input('per_page', 10);
        $contacts = $query->orderBy('firstname', 'asc')->paginate($perPage);

        // Summary counts
        $totalContacts = Contact::count();
        $activeContacts = Contact::where('active', true)->count();
        $inactiveContacts = Contact::where('active', false)->count();
        
        // Count contacts who have logged in today (where last_login is today)
        $loggedInToday = Contact::whereDate('last_login', Carbon::today())->count();

        return response()->json([
            'contacts' => $contacts,
            'summary' => [
                'total_contacts' => $totalContacts,
                'active_contacts' => $activeContacts,
                'inactive_contacts' => $inactiveContacts,
                'contacts_logged_in_today' => $loggedInToday,
            ]
        ]);
    }

    /**
     * Store a newly created contact.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'firstname' => 'required|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'email' => 'required|email|unique:contacts,email|max:255',
            'phonenumber' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6',
            'active' => 'boolean',
            'is_primary' => 'boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        // If this is set as primary contact, unset other primary contacts for this client
        if (!empty($validated['is_primary']) && $validated['is_primary']) {
            Contact::where('client_id', $validated['client_id'])->update(['is_primary' => false]);
        }

        $contact = Contact::create($validated);

        return response()->json($contact->load('client'), 201);
    }

    /**
     * Update the specified contact.
     */
    public function update(Request $request, $id)
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json(['message' => 'Contact not found'], 404);
        }

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'firstname' => 'required|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'email' => 'required|email|unique:contacts,email,' . $id . '|max:255',
            'phonenumber' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6',
            'active' => 'boolean',
            'is_primary' => 'boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        if (isset($validated['is_primary']) && $validated['is_primary']) {
            Contact::where('client_id', $validated['client_id'])->update(['is_primary' => false]);
        }

        $contact->update($validated);

        return response()->json($contact->load('client'));
    }

    /**
     * Remove the specified contact.
     */
    public function destroy($id)
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json(['message' => 'Contact not found'], 404);
        }

        $contact->delete();

        return response()->json(['message' => 'Contact deleted successfully']);
    }

    /**
     * Toggle active status of contact.
     */
    public function updateStatus(Request $request, $id)
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json(['message' => 'Contact not found'], 404);
        }

        $validated = $request->validate([
            'active' => 'required|boolean'
        ]);

        $contact->update([
            'active' => $validated['active']
        ]);

        return response()->json([
            'message' => 'Contact status updated successfully',
            'contact' => $contact
        ]);
    }
}
