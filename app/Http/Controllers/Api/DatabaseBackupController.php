<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DatabaseBackup;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class DatabaseBackupController extends Controller
{
    public function index()
    {
        $backups = DatabaseBackup::where('is_auto_flag', false)
            ->orderBy('created_at', 'desc')
            ->get();

        $autoBackup = DatabaseBackup::where('auto_backup', true)->exists();

        return response()->json([
            'backups' => $backups,
            'auto_backup' => $autoBackup,
        ]);
    }

    public function store()
    {
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $dbHost = config('database.connections.mysql.host');
        $dbPort = config('database.connections.mysql.port');

        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "backup_{$dbName}_{$timestamp}.sql";
        $backupDir = storage_path('app/backups');

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filePath = "{$backupDir}/{$filename}";

        $exitCode = $this->runMysqldump($dbHost, $dbPort, $dbUser, $dbPass, $dbName, $filePath);

        if ($exitCode !== 0) {
            return response()->json([
                'message' => 'Failed to create database backup. Please ensure mysqldump is available on your system.',
            ], 500);
        }

        if (!file_exists($filePath)) {
            return response()->json(['message' => 'Backup file was not created.'], 500);
        }

        $backupSize = filesize($filePath);

        $backup = DatabaseBackup::create([
            'filename' => $filename,
            'file_path' => $filePath,
            'backup_size' => $this->formatSize($backupSize),
            'is_auto_flag' => false,
            'auto_backup' => false,
        ]);

        ActivityLog::log("Created database backup: {$filename}");

        return response()->json([
            'message' => 'Database backup created successfully',
            'backup' => $backup,
        ], 201);
    }

    public function download($id)
    {
        $backup = DatabaseBackup::find($id);

        if (!$backup) {
            return response()->json(['message' => 'Backup not found'], 404);
        }

        if (!file_exists($backup->file_path)) {
            return response()->json(['message' => 'Backup file not found on disk'], 404);
        }

        return response()->download($backup->file_path, $backup->filename);
    }

    public function destroy($id)
    {
        $backup = DatabaseBackup::find($id);

        if (!$backup) {
            return response()->json(['message' => 'Backup not found'], 404);
        }

        if (file_exists($backup->file_path)) {
            unlink($backup->file_path);
        }

        $filename = $backup->filename;
        $backup->delete();

        ActivityLog::log("Deleted database backup: {$filename}");

        return response()->json(['message' => 'Backup deleted']);
    }

    public function toggleAutoBackup(Request $request)
    {
        $enabled = $request->input('enabled', true);

        $autoFlag = DatabaseBackup::where('is_auto_flag', true)->first();

        if ($enabled) {
            if (!$autoFlag) {
                DatabaseBackup::create([
                    'filename' => 'auto_backup_flag',
                    'file_path' => 'auto_backup_flag',
                    'backup_size' => null,
                    'is_auto_flag' => true,
                    'auto_backup' => true,
                ]);
            } else {
                $autoFlag->update(['auto_backup' => true]);
            }
            ActivityLog::log('Enabled automatic database backup');
        } else {
            if ($autoFlag) {
                $autoFlag->update(['auto_backup' => false]);
            }
            ActivityLog::log('Disabled automatic database backup');
        }

        return response()->json([
            'message' => $enabled ? 'Auto backup enabled' : 'Auto backup disabled',
            'auto_backup' => $enabled,
        ]);
    }

    private function runMysqldump($host, $port, $user, $pass, $db, $filePath)
    {
        $paths = [
            'mysqldump',
            '/Applications/XAMPP/bin/mysqldump',
            '/Applications/MAMP/Library/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/usr/bin/mysqldump',
        ];

        foreach ($paths as $mysqldump) {
            $cmd = str_contains($mysqldump, '/')
                ? '"' . $mysqldump . '"'
                : $mysqldump;

            $command = sprintf(
                '%s --host=%s --port=%s --user=%s --password=%s %s > %s 2>&1',
                $cmd,
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($user),
                escapeshellarg($pass),
                escapeshellarg($db),
                escapeshellarg($filePath)
            );

            exec($command, $output, $exitCode);

            if ($exitCode === 0) {
                return 0;
            }
        }

        return $exitCode ?? 1;
    }

    private function formatSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
