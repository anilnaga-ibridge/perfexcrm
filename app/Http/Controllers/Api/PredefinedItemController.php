<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PredefinedItem;

class PredefinedItemController extends Controller
{
    public function index(Request $request)
    {
        $query = PredefinedItem::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 25);
        $items = $query->orderBy('name', 'asc')->paginate($perPage);

        return response()->json([
            'items' => $items,
        ]);
    }

    public function store(Request $request)
    {
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

    public function destroy($id)
    {
        $item = PredefinedItem::find($id);
        if (!$item) return response()->json(['message' => 'Predefined item not found'], 404);
        $item->delete();
        return response()->json(['message' => 'Predefined item deleted']);
    }
}
