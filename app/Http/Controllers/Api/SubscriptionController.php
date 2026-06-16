<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Models\Client;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $query = Subscription::with('client:id,company', 'project:id,name');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('stripe_plan', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($c) => $c->where('company', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->input('client_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = $request->input('per_page', 25);
        $subscriptions = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Summary stats
        $stats = [
            'total'            => Subscription::count(),
            'active'           => Subscription::where('status', 'active')->count(),
            'inactive'         => Subscription::where('status', 'inactive')->count(),
            'cancelled'        => Subscription::where('status', 'cancelled')->count(),
            'total_amount'     => Subscription::sum('amount'),
            'active_amount'    => Subscription::where('status', 'active')->sum('amount'),
        ];

        return response()->json([
            'subscriptions' => $subscriptions,
            'stats'         => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'client_id'           => 'required|exists:clients,id',
            'project_id'          => 'nullable|exists:projects,id',
            'amount'              => 'required|numeric|min:0',
            'billing_period'      => 'required|string|in:monthly,yearly,bi-weekly',
            'status'              => 'required|string|in:active,inactive,cancelled',
            'start_date'          => 'required|date',
            'stripe_plan'         => 'nullable|string|max:255',
            'quantity'            => 'nullable|integer|min:1',
            'currency'            => 'nullable|string|max:10',
            'tax_1'               => 'nullable|string|max:255',
            'tax_2'               => 'nullable|string|max:255',
            'terms'               => 'nullable|string',
            'description'         => 'nullable|string',
            'include_description' => 'nullable|boolean',
        ]);

        $subscription = Subscription::create($validated);
        return response()->json($subscription->load('client', 'project'), 201);
    }

    public function show($id)
    {
        $subscription = Subscription::with('client')->find($id);
        if (!$subscription) return response()->json(['message' => 'Subscription not found'], 404);
        return response()->json($subscription);
    }

    public function update(Request $request, $id)
    {
        $subscription = Subscription::find($id);
        if (!$subscription) return response()->json(['message' => 'Subscription not found'], 404);

        $validated = $request->validate([
            'name'                => 'sometimes|string|max:255',
            'project_id'          => 'nullable|exists:projects,id',
            'amount'              => 'sometimes|numeric|min:0',
            'billing_period'      => 'sometimes|string|in:monthly,yearly,bi-weekly',
            'status'              => 'sometimes|string|in:active,inactive,cancelled',
            'start_date'          => 'sometimes|date',
            'stripe_plan'         => 'nullable|string|max:255',
            'quantity'            => 'nullable|integer|min:1',
            'currency'            => 'nullable|string|max:10',
            'tax_1'               => 'nullable|string|max:255',
            'tax_2'               => 'nullable|string|max:255',
            'terms'               => 'nullable|string',
            'description'         => 'nullable|string',
            'include_description' => 'nullable|boolean',
        ]);

        $subscription->update($validated);
        return response()->json($subscription->load('client', 'project'));
    }

    public function destroy($id)
    {
        $subscription = Subscription::find($id);
        if (!$subscription) return response()->json(['message' => 'Subscription not found'], 404);
        $subscription->delete();
        return response()->json(['message' => 'Subscription deleted']);
    }
}
