<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ClientFile;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Storage;

class ClientFileController extends Controller
{
    /**
     * List files for a customer.
     */
    public function index(Request $request, $clientId)
    {
        $client = Client::find($clientId);
        if (!$client) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $query = ClientFile::where('client_id', $clientId);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('file_name', 'like', "%{$search}%");
        }

        $files = $query->orderBy('created_at', 'desc')->get();

        return response()->json($files);
    }

    /**
     * Upload a file for a customer.
     */
    public function store(Request $request, $clientId)
    {
        $client = Client::find($clientId);
        if (!$client) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $request->validate([
            'file' => 'required|file|max:20480', // 20MB limit
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();

        // Store file in storage/app/public/client_files/{client_id}/
        $path = $file->store("client_files/{$clientId}", 'public');

        $clientFile = ClientFile::create([
            'client_id' => $clientId,
            'file_name' => $originalName,
            'file_path' => 'storage/' . $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getClientMimeType(),
            'visible_to_customer' => $request->boolean('visible_to_customer', false),
        ]);

        ActivityLog::log("Uploaded file: {$originalName} for customer #{$clientId}");

        return response()->json($clientFile, 201);
    }

    /**
     * Toggle visibility of file to customers.
     */
    public function updateStatus(Request $request, $id)
    {
        $file = ClientFile::find($id);
        if (!$file) {
            return response()->json(['message' => 'File not found'], 404);
        }

        $validated = $request->validate([
            'visible_to_customer' => 'required|boolean',
        ]);

        $file->update([
            'visible_to_customer' => $validated['visible_to_customer'],
        ]);

        ActivityLog::log("Updated visibility status of file: {$file->file_name} for customer #{$file->client_id}");

        return response()->json($file);
    }

    /**
     * Delete a customer file.
     */
    public function destroy($id)
    {
        $file = ClientFile::find($id);
        if (!$file) {
            return response()->json(['message' => 'File not found'], 404);
        }

        // Delete from storage disk
        $relativeStoragePath = str_replace('storage/', '', $file->file_path);
        Storage::disk('public')->delete($relativeStoragePath);

        $fileName = $file->file_name;
        $clientId = $file->client_id;
        $file->delete();

        ActivityLog::log("Deleted file: {$fileName} for customer #{$clientId}");

        return response()->json(['message' => 'File deleted successfully']);
    }
}
