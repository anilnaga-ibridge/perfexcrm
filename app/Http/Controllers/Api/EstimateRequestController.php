<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EstimateRequest;
use App\Models\ActivityLog;

class EstimateRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = EstimateRequest::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = $request->input('per_page', 25);
        $requests = $query->orderBy('created_at', 'desc')->paginate($perPage);

        $stats = [
            'total'     => EstimateRequest::count(),
            'pending'   => EstimateRequest::where('status', 'pending')->count(),
            'converted' => EstimateRequest::where('status', 'converted')->count(),
            'declined'  => EstimateRequest::where('status', 'declined')->count(),
        ];

        return response()->json(['requests' => $requests, 'stats' => $stats]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email',
            'message' => 'nullable|string',
            'status'  => 'nullable|string|in:pending,converted,declined',
        ]);

        $req = EstimateRequest::create($validated);
        return response()->json($req, 201);
    }

    public function show($id)
    {
        $req = EstimateRequest::find($id);
        if (!$req) return response()->json(['message' => 'Request not found'], 404);
        return response()->json($req);
    }

    public function update(Request $request, $id)
    {
        $req = EstimateRequest::find($id);
        if (!$req) return response()->json(['message' => 'Request not found'], 404);

        $validated = $request->validate([
            'status' => 'sometimes|string|in:pending,converted,declined',
        ]);

        $req->update($validated);
        ActivityLog::log("Updated estimate request from {$req->name} to status: {$req->status}");

        return response()->json($req);
    }

    public function destroy($id)
    {
        $req = EstimateRequest::find($id);
        if (!$req) return response()->json(['message' => 'Request not found'], 404);
        $req->delete();
        return response()->json(['message' => 'Request deleted']);
    }
}
