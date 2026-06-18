<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EstimateRequestForm;
use App\Models\User;

class EstimateRequestFormController extends Controller
{
    public function index(Request $request)
    {
        $query = EstimateRequestForm::with('assigned');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = $request->input('per_page', 25);
        $forms = $query->orderBy('created_at', 'desc')->paginate($perPage);

        $stats = [
            'total'      => EstimateRequestForm::count(),
            'processing' => EstimateRequestForm::where('status', 'processing')->count(),
            'active'     => EstimateRequestForm::where('status', 'active')->count(),
            'inactive'   => EstimateRequestForm::where('status', 'inactive')->count(),
        ];

        $staff = User::where('active', true)->orderBy('name')->get(['id', 'name', 'profile_image']);

        return response()->json(['forms' => $forms, 'stats' => $stats, 'staff' => $staff]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'email'                => 'nullable|email',
            'tags'                 => 'nullable|string',
            'assigned_to'          => 'nullable|exists:users,id',
            'status'               => 'nullable|string|in:processing,active,inactive',
            'language'             => 'nullable|string|max:50',
            'recaptcha_enabled'    => 'nullable|boolean',
            'submit_btn_text'      => 'nullable|string|max:255',
            'submit_btn_bg_color'  => 'nullable|string|max:20',
            'submit_btn_text_color' => 'nullable|string|max:20',
            'submission_action'    => 'nullable|string|in:message,redirect',
            'submission_message'   => 'nullable|string',
            'submission_redirect_url' => 'nullable|string|max:500',
            'notify_enabled'       => 'nullable|boolean',
            'notify_type'          => 'nullable|string|in:specific,responsible',
            'notify_staff_ids'     => 'nullable|string',
        ]);

        $form = EstimateRequestForm::create($validated);
        return response()->json($form->load('assigned'), 201);
    }

    public function show($id)
    {
        $form = EstimateRequestForm::with('assigned')->find($id);
        if (!$form) return response()->json(['message' => 'Form not found'], 404);
        return response()->json($form);
    }

    public function update(Request $request, $id)
    {
        $form = EstimateRequestForm::find($id);
        if (!$form) return response()->json(['message' => 'Form not found'], 404);

        $validated = $request->validate([
            'name'                 => 'sometimes|string|max:255',
            'email'                => 'nullable|email',
            'tags'                 => 'nullable|string',
            'assigned_to'          => 'nullable|exists:users,id',
            'status'               => 'nullable|string|in:processing,active,inactive',
            'language'             => 'nullable|string|max:50',
            'recaptcha_enabled'    => 'nullable|boolean',
            'submit_btn_text'      => 'nullable|string|max:255',
            'submit_btn_bg_color'  => 'nullable|string|max:20',
            'submit_btn_text_color' => 'nullable|string|max:20',
            'submission_action'    => 'nullable|string|in:message,redirect',
            'submission_message'   => 'nullable|string',
            'submission_redirect_url' => 'nullable|string|max:500',
            'notify_enabled'       => 'nullable|boolean',
            'notify_type'          => 'nullable|string|in:specific,responsible',
            'notify_staff_ids'     => 'nullable|string',
        ]);

        $form->update($validated);
        return response()->json($form->load('assigned'));
    }

    public function destroy($id)
    {
        $form = EstimateRequestForm::find($id);
        if (!$form) return response()->json(['message' => 'Form not found'], 404);
        $form->delete();
        return response()->json(['message' => 'Form deleted']);
    }
}
