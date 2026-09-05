<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PredefinedItem;

class PredefinedItemController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user() && !$request->user()->hasPermission('Items.view')) {
            abort(403, 'Unauthorized. Missing required permission: Items.view');
        }

        $query = PredefinedItem::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $perPage = min($request->input('per_page', 25), 100);
        $items = $query->orderBy('name', 'asc')->paginate($perPage);

        return response()->json([
            'items' => $items,
        ]);
    }

    public function store(Request $request)
    {
        if ($request->user() && !$request->user()->hasPermission('Items.create')) {
            abort(403, 'Unauthorized. Missing required permission: Items.create');
        }

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'rate'        => 'required|numeric|min:0',
            'tax_rate'    => 'nullable|numeric|min:0|max:100',
            'unit'        => 'nullable|string|max:50',
        ]);

        $item = PredefinedItem::create($validated);
        return response()->json($item, 201);
    }

    public function show($id)
    {
        $item = PredefinedItem::find($id);
        if (!$item) return response()->json(['message' => 'Predefined item not found'], 404);
        return response()->json($item);
    }

    public function update(Request $request, $id)
    {
        if ($request->user() && !$request->user()->hasPermission('Items.edit')) {
            abort(403, 'Unauthorized. Missing required permission: Items.edit');
        }

        $item = PredefinedItem::find($id);
        if (!$item) return response()->json(['message' => 'Predefined item not found'], 404);

        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'rate'        => 'sometimes|numeric|min:0',
            'tax_rate'    => 'nullable|numeric|min:0|max:100',
            'unit'        => 'nullable|string|max:50',
        ]);

        $item->update($validated);
        return response()->json($item);
    }

    public function destroy(Request $request, $id)
    {
        if ($request->user() && !$request->user()->hasPermission('Items.delete')) {
            abort(403, 'Unauthorized. Missing required permission: Items.delete');
        }

        $item = PredefinedItem::find($id);
        if (!$item) return response()->json(['message' => 'Predefined item not found'], 404);
        $item->delete();
        return response()->json(['message' => 'Predefined item deleted']);
    }
}
