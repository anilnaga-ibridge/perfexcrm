<?php

namespace App\Console\Commands;

use App\Models\DatabaseBackup;
use App\Models\ActivityLog;
use Illuminate\Console\Command;

class DatabaseBackupCommand extends Command
{
    protected $signature = 'backup:database';
    protected $description = 'Create an automatic database backup';

    public function handle()
    {
        $autoBackup = DatabaseBackup::where('is_auto_flag', true)
            ->where('auto_backup', true)
            ->exists();

        if (!$autoBackup) {
            $this->info('Auto backup is disabled. Skipping.');
            return 0;
        }

        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $dbHost = config('database.connections.mysql.host');
        $dbPort = config('database.connections.mysql.port');

        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "auto_backup_{$dbName}_{$timestamp}.sql";
        $backupDir = storage_path('app/backups');

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filePath = "{$backupDir}/{$filename}";

        $paths = [
            'mysqldump',
            '/Applications/XAMPP/bin/mysqldump',
            '/Applications/MAMP/Library/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/usr/bin/mysqldump',
        ];

        $exitCode = 1;

        foreach ($paths as $mysqldump) {
            $cmd = str_contains($mysqldump, '/')
                ? '"' . $mysqldump . '"'
                : $mysqldump;

            $command = sprintf(
                '%s --host=%s --port=%s --user=%s --password=%s %s > %s 2>&1',
                $cmd,
                escapeshellarg($dbHost),
                escapeshellarg($dbPort),
                escapeshellarg($dbUser),
                escapeshellarg($dbPass),
                escapeshellarg($dbName),
                escapeshellarg($filePath)
            );

            exec($command, $output, $exitCode);

            if ($exitCode === 0) {
                break;
            }
        }

        if ($exitCode !== 0 || !file_exists($filePath)) {
            $this->error('Auto backup failed.');
            return 1;
        }

        $backupSize = filesize($filePath);

        DatabaseBackup::create([
            'filename' => $filename,
            'file_path' => $filePath,
            'backup_size' => $this->formatSize($backupSize),
            'is_auto_flag' => false,
            'auto_backup' => false,
        ]);

        ActivityLog::log("Auto database backup created: {$filename}");

        $this->info("Auto backup created: {$filename}");
        return 0;
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
