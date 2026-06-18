<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KbCategory;
use App\Models\KbArticle;

class KbCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = KbCategory::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 25);
        $categories = $query->withCount('articles')->orderBy('order_num')->orderBy('name')->paginate($perPage);

        return response()->json(['categories' => $categories]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:kb_categories,slug',
            'color'       => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'order_num'   => 'nullable|integer|min:0',
            'disabled'    => 'nullable|boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        }

        $category = KbCategory::create($validated);
        return response()->json($category, 201);
    }

    public function show($id)
    {
        $category = KbCategory::with('articles')->find($id);
        if (!$category) return response()->json(['message' => 'Category not found'], 404);
        return response()->json($category);
    }

    public function update(Request $request, $id)
    {
        $category = KbCategory::find($id);
        if (!$category) return response()->json(['message' => 'Category not found'], 404);

        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:kb_categories,slug,' . $id,
            'color'       => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'order_num'   => 'nullable|integer|min:0',
            'disabled'    => 'nullable|boolean',
        ]);

        $category->update($validated);
        return response()->json($category);
    }

    public function destroy($id)
    {
        $category = KbCategory::find($id);
        if (!$category) return response()->json(['message' => 'Category not found'], 404);

        // Unlink articles
        KbArticle::where('category_id', $id)->update(['category_id' => null]);

        $category->delete();
        return response()->json(['message' => 'Category deleted']);
    }
}
