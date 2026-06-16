<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KbArticle;
use App\Models\ActivityLog;

class KbController extends Controller
{
    public function index(Request $request)
    {
        $query = KbArticle::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        $perPage = $request->input('per_page', 25);
        $articles = $query->orderBy('views_count', 'desc')->paginate($perPage);

        $stats = [
            'total'     => KbArticle::count(),
            'published' => KbArticle::where('status', 'published')->count(),
            'draft'     => KbArticle::where('status', 'draft')->count(),
            'total_views' => KbArticle::sum('views_count'),
        ];

        return response()->json(['articles' => $articles, 'stats' => $stats]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'content'     => 'required|string',
            'category_id' => 'nullable|integer',
            'status'      => 'required|string|in:published,draft',
        ]);

        $article = KbArticle::create($validated);
        ActivityLog::log("Created KB article: {$article->title}");

        return response()->json($article, 201);
    }

    public function show($id)
    {
        $article = KbArticle::find($id);
        if (!$article) return response()->json(['message' => 'Article not found'], 404);
        $article->increment('views_count');
        return response()->json($article);
    }

    public function update(Request $request, $id)
    {
        $article = KbArticle::find($id);
        if (!$article) return response()->json(['message' => 'Article not found'], 404);

        $validated = $request->validate([
            'title'       => 'sometimes|string|max:255',
            'content'     => 'sometimes|string',
            'category_id' => 'nullable|integer',
            'status'      => 'sometimes|string|in:published,draft',
        ]);

        $article->update($validated);
        ActivityLog::log("Updated KB article: {$article->title}");

        return response()->json($article);
    }

    public function destroy($id)
    {
        $article = KbArticle::find($id);
        if (!$article) return response()->json(['message' => 'Article not found'], 404);
        $article->delete();
        return response()->json(['message' => 'Article deleted']);
    }
}
