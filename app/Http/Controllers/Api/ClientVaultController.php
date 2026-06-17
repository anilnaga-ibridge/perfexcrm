<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ClientVault;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Crypt;

class ClientVaultController extends Controller
{
    /**
     * List vault entries for a client, respecting visibility rules.
     */
    public function index(Request $request, $clientId)
    {
        $client = Client::find($clientId);
        if (!$client) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $user = $request->user();
        $isAdmin = $user->role === 'admin';

        $query = ClientVault::where('client_id', $clientId);

        if (!$isAdmin) {
            $query->where(function ($q) use ($user) {
                $q->where('visibility', 'all_staff')
                    ->orWhere(function ($sub) use ($user) {
                        $sub->where('visibility', 'me')
                            ->where('created_by', $user->id);
                    });
            });
        }

        $vaults = $query->with('creator:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($vaults);
    }

    /**
     * Store a new vault entry.
     */
    public function store(Request $request, $clientId)
    {
        $client = Client::find($clientId);
        if (!$client) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $validated = $request->validate([
            'server_address' => 'required|string|max:255',
            'port' => 'nullable|integer',
            'username' => 'required|string|max:255',
            'password' => 'required|string',
            'short_description' => 'nullable|string',
            'visibility' => 'required|string|in:all_staff,admins,me',
            'share_in_projects' => 'boolean',
        ]);

        $validated['client_id'] = $clientId;
        $validated['created_by'] = $request->user()->id;
        $validated['password'] = Crypt::encryptString($validated['password']);

        $vault = ClientVault::create($validated);

        ActivityLog::log("Created vault entry for customer #{$clientId}");

        $vault->load('creator:id,name');

        return response()->json($vault, 201);
    }

    /**
     * Update an existing vault entry.
     */
    public function update(Request $request, $id)
    {
        $vault = ClientVault::find($id);
        if (!$vault) {
            return response()->json(['message' => 'Vault entry not found'], 404);
        }

        $user = $request->user();
        $isAdmin = $user->role === 'admin';

        // Only administrator or creator can update
        if (!$isAdmin && $vault->created_by !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'server_address' => 'required|string|max:255',
            'port' => 'nullable|integer',
            'username' => 'required|string|max:255',
            'password' => 'required|string',
            'short_description' => 'nullable|string',
            'visibility' => 'required|string|in:all_staff,admins,me',
            'share_in_projects' => 'boolean',
        ]);

        $validated['password'] = Crypt::encryptString($validated['password']);

        $vault->update($validated);

        ActivityLog::log("Updated vault entry #{$id} for customer #{$vault->client_id}");

        $vault->load('creator:id,name');

        return response()->json($vault);
    }

    /**
     * Decrypt and reveal a vault entry's password.
     */
    public function decrypt(Request $request, $id)
    {
        $vault = ClientVault::find($id);
        if (!$vault) {
            return response()->json(['message' => 'Vault entry not found'], 404);
        }

        $user = $request->user();
        $isAdmin = $user->role === 'admin';

        // Check accessibility
        $canAccess = false;
        if ($isAdmin) {
            $canAccess = true;
        } else {
            if ($vault->visibility === 'all_staff') {
                $canAccess = true;
            } elseif ($vault->visibility === 'me' && $vault->created_by === $user->id) {
                $canAccess = true;
            }
        }

        if (!$canAccess) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $decrypted = Crypt::decryptString($vault->password);
            return response()->json(['password' => $decrypted]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to decrypt password'], 500);
        }
    }

    /**
     * Delete a vault entry.
     */
    public function destroy(Request $request, $id)
    {
        $vault = ClientVault::find($id);
        if (!$vault) {
            return response()->json(['message' => 'Vault entry not found'], 404);
        }

        $user = $request->user();
        $isAdmin = $user->role === 'admin';

        // Only administrator or creator can delete
        if (!$isAdmin && $vault->created_by !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $clientId = $vault->client_id;
        $vault->delete();

        ActivityLog::log("Deleted vault entry #{$id} for customer #{$clientId}");

        return response()->json(['message' => 'Vault entry deleted successfully']);
    }
}
