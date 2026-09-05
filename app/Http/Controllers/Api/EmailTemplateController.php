<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    /**
     * Get all email templates.
     */
    public function index(): JsonResponse
    {
        $templates = EmailTemplate::all();

        return response()->json([
            'success' => true,
            'data'    => $templates,
        ]);
    }

    /**
     * Update or create a single template.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key'        => 'required|string|max:100',
            'name'       => 'required|string|max:255',
            'subject'    => 'required|string',
            'body'       => 'required|string',
            'type'       => 'nullable|string|max:100',
            'audience'   => 'nullable|string|max:50',
            'from_name'  => 'nullable|string|max:255',
            'active'     => 'boolean',
        ]);

        $template = EmailTemplate::updateOrCreate(
            ['key' => $validated['key']],
            $validated
        );

        return response()->json([
            'success' => true,
            'message' => 'Email template saved successfully.',
            'data'    => $template,
        ]);
    }

    /**
     * Bulk save email templates.
     */
    public function bulkStore(Request $request): JsonResponse
    {
        $templates = $request->input('templates', []);

        foreach ($templates as $item) {
            if (empty($item['key']) && !empty($item['name'])) {
                $item['key'] = \Str::slug($item['name'], '_');
            }

            if (!empty($item['key'])) {
                EmailTemplate::updateOrCreate(
                    ['key' => $item['key']],
                    [
                        'name'      => $item['name'] ?? $item['key'],
                        'type'      => $item['type'] ?? 'General',
                        'audience'  => $item['audience'] ?? 'employee',
                        'subject'   => $item['subject'] ?? '',
                        'body'      => $item['body'] ?? '',
                        'from_name' => $item['from_name'] ?? null,
                        'active'    => isset($item['active']) ? (bool)$item['active'] : true,
                    ]
                );
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'All email templates updated successfully.',
        ]);
    }

    /**
     * Upload company logo image.
     */
    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'logo.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);

            $url = asset('images/' . $filename) . '?v=' . time();

            return response()->json([
                'success' => true,
                'message' => 'Company logo uploaded successfully!',
                'url'     => $url,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No image file uploaded.',
        ], 400);
    }
}
