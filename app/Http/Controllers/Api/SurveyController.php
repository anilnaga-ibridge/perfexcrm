<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    public function index(Request $request)
    {
        $query = Survey::query();
        if ($search = $request->search) {
            $query->where('subject', 'like', "%{$search}%");
        }
        return response()->json([
            'surveys' => $query->orderBy('id', 'desc')->paginate($request->per_page ?? 25)
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'view_description' => 'nullable|string',
            'email_description' => 'nullable|string',
            'include_link' => 'boolean',
            'from_email' => 'nullable|email|max:255',
            'redirect_url' => 'nullable|url|max:500',
            'disabled' => 'boolean',
            'logged_in_only' => 'boolean',
        ]);
        $data['date_created'] = now();
        $data['active'] = !($data['disabled'] ?? false);
        $survey = Survey::create($data);
        return response()->json(['survey' => $survey], 201);
    }

    public function show($id)
    {
        return response()->json(['survey' => Survey::findOrFail($id)]);
    }

    public function update(Request $request, $id)
    {
        $survey = Survey::findOrFail($id);
        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'view_description' => 'nullable|string',
            'email_description' => 'nullable|string',
            'include_link' => 'boolean',
            'from_email' => 'nullable|email|max:255',
            'redirect_url' => 'nullable|url|max:500',
            'disabled' => 'boolean',
            'logged_in_only' => 'boolean',
        ]);
        $data['active'] = !($data['disabled'] ?? false);
        $survey->update($data);
        return response()->json(['survey' => $survey]);
    }

    public function destroy($id)
    {
        Survey::findOrFail($id)->delete();
        return response()->json(['message' => 'Survey deleted']);
    }
}
