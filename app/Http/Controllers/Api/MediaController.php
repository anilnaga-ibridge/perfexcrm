<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MediaItem;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $parentId = $request->input('parent_id');

        // Get folders and files in current directory
        $items = MediaItem::where('parent_id', $parentId)
            ->orderBy('is_directory', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        // Calculate breadcrumbs
        $breadcrumbs = [];
        $current = $parentId ? MediaItem::find($parentId) : null;
        while ($current) {
            array_unshift($breadcrumbs, [
                'id'   => $current->id,
                'name' => $current->name
            ]);
            $current = $current->parent_id ? MediaItem::find($current->parent_id) : null;
        }

        return response()->json([
            'items'       => $items,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    public function store(Request $request)
    {
        $parentId = $request->input('parent_id');

        if ($request->hasFile('file')) {
            $request->validate([
                'file' => 'required|file|max:10240', // 10MB max
            ]);

            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            
            // Store file in public storage disk under media/ folder
            $path = $file->store('media', 'public');

            $media = MediaItem::create([
                'name'         => $originalName,
                'file_path'    => 'storage/' . $path,
                'is_directory' => false,
                'parent_id'    => $parentId,
                'size'         => $file->getSize(),
                'mime_type'    => $file->getClientMimeType(),
            ]);

            ActivityLog::log("Uploaded media file: {$originalName}");
            return response()->json($media, 201);
        }

        // Folder creation
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $media = MediaItem::create([
            'name'         => $validated['name'],
            'is_directory' => true,
            'parent_id'    => $parentId,
        ]);

        ActivityLog::log("Created media folder: {$validated['name']}");
        return response()->json($media, 201);
    }

    public function destroy($id)
    {
        $media = MediaItem::find($id);
        if (!$media) return response()->json(['message' => 'Media item not found'], 404);

        $name = $media->name;
        
        if ($media->is_directory) {
            // Delete child files from storage disk recursively
            $this->deleteRecursive($media);
            $media->delete();
            ActivityLog::log("Deleted media folder: {$name}");
        } else {
            // Delete file from disk
            $relativeStoragePath = str_replace('storage/', '', $media->file_path);
            Storage::disk('public')->delete($relativeStoragePath);
            $media->delete();
            ActivityLog::log("Deleted media file: {$name}");
        }

        return response()->json(['message' => 'Item deleted successfully']);
    }

    protected function deleteRecursive($folder)
    {
        $children = MediaItem::where('parent_id', $folder->id)->get();
        foreach ($children as $child) {
            if ($child->is_directory) {
                $this->deleteRecursive($child);
            } else {
                $relativeStoragePath = str_replace('storage/', '', $child->file_path);
                Storage::disk('public')->delete($relativeStoragePath);
            }
            $child->delete();
        }
    }
}
