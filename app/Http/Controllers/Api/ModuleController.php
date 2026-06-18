<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ModuleController extends Controller
{
    /**
     * List all modules
     */
    public function index(Request $request): JsonResponse
    {
        $query = Module::query();

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $modules = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $modules,
        ]);
    }

    /**
     * Store a newly uploaded module
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'module_file' => 'required|file|mimes:zip',
        ]);

        $file = $request->file('module_file');
        $filename = $file->getClientOriginalName();

        // Store the uploaded zip
        $path = $file->storeAs('modules', $filename, 'public');

        // Extract and read module info
        $tempDir = storage_path('app/temp_module_extract');
        $zip = new ZipArchive();

        if ($zip->open(storage_path('app/public/modules/' . $filename)) === true) {
            $zip->extractTo($tempDir);
            $zip->close();

            // Look for module.json or module_info.json
            $moduleInfo = $this->readModuleInfo($tempDir);

            // Cleanup temp directory
            $this->deleteDirectory($tempDir);

            if ($moduleInfo) {
                $module = Module::create([
                    'name' => $moduleInfo['name'] ?? pathinfo($filename, PATHINFO_FILENAME),
                    'description' => $moduleInfo['description'] ?? '',
                    'version' => $moduleInfo['version'] ?? '1.0.0',
                    'slug' => $moduleInfo['slug'] ?? pathinfo($filename, PATHINFO_FILENAME),
                    'is_active' => true,
                    'is_installed' => true,
                    'file_path' => $path,
                    'settings_link' => $moduleInfo['settings_link'] ?? null,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Module uploaded and installed successfully.',
                    'data' => $module,
                ], 201);
            }
        }

        // If no module.json found, create with basic info
        $module = Module::create([
            'name' => pathinfo($filename, PATHINFO_FILENAME),
            'description' => 'Uploaded module',
            'version' => '1.0.0',
            'slug' => pathinfo($filename, PATHINFO_FILENAME),
            'is_active' => true,
            'is_installed' => true,
            'file_path' => $path,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Module uploaded successfully.',
            'data' => $module,
        ], 201);
    }

    /**
     * Toggle module active/inactive status
     */
    public function toggleStatus($id): JsonResponse
    {
        $module = Module::findOrFail($id);
        $module->is_active = !$module->is_active;
        $module->save();

        return response()->json([
            'success' => true,
            'message' => $module->is_active ? 'Module activated.' : 'Module deactivated.',
            'data' => $module,
        ]);
    }

    /**
     * Deactivate a module
     */
    public function deactivate($id): JsonResponse
    {
        $module = Module::findOrFail($id);
        $module->is_active = false;
        $module->save();

        return response()->json([
            'success' => true,
            'message' => 'Module deactivated.',
            'data' => $module,
        ]);
    }

    /**
     * Activate a module
     */
    public function activate($id): JsonResponse
    {
        $module = Module::findOrFail($id);
        $module->is_active = true;
        $module->save();

        return response()->json([
            'success' => true,
            'message' => 'Module activated.',
            'data' => $module,
        ]);
    }

    /**
     * Delete a module
     */
    public function destroy($id): JsonResponse
    {
        $module = Module::findOrFail($id);

        // Delete the file if it exists
        if ($module->file_path && Storage::disk('public')->exists($module->file_path)) {
            Storage::disk('public')->delete($module->file_path);
        }

        $module->delete();

        return response()->json([
            'success' => true,
            'message' => 'Module deleted successfully.',
        ]);
    }

    /**
     * Read module info from extracted zip
     */
    private function readModuleInfo($path): ?array
    {
        $infoFiles = ['module.json', 'module_info.json', 'package.json'];

        foreach ($infoFiles as $file) {
            $filePath = $path . '/' . $file;
            if (file_exists($filePath)) {
                $content = file_get_contents($filePath);
                $info = json_decode($content, true);
                if ($info && isset($info['name'])) {
                    return $info;
                }
            }
        }

        return null;
    }

    /**
     * Recursively delete a directory
     */
    private function deleteDirectory($path): void
    {
        if (is_dir($path)) {
            $objects = scandir($path);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($path . "/" . $object)) {
                        $this->deleteDirectory($path . "/" . $object);
                    } else {
                        unlink($path . "/" . $object);
                    }
                }
            }
            rmdir($path);
        }
    }
}
