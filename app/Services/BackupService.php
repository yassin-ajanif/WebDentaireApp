<?php

namespace App\Services;

use Illuminate\Support\Facades\Date;

class BackupService
{
    public function create(string $path, ?string $pgBinDir = null): string
    {
        $path = rtrim($path, '\\/');
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $filename = 'backup-' . Date::now()->format('Y-m-d-His') . '.sql';
        $filePath = $path . DIRECTORY_SEPARATOR . $filename;

        $db = config('database.connections.pgsql');

        putenv('PGPASSWORD=' . $db['password']);

        $pgDump = $this->findPgDump($pgBinDir);

        $command = sprintf(
            '%s --host=%s --port=%s --username=%s --no-password --format=c --file=%s %s 2>&1',
            escapeshellarg($pgDump),
            escapeshellarg($db['host']),
            escapeshellarg($db['port']),
            escapeshellarg($db['username']),
            escapeshellarg($filePath),
            escapeshellarg($db['database']),
        );

        $output = null;
        $exitCode = null;
        exec($command, $output, $exitCode);

        putenv('PGPASSWORD');

        if ($exitCode !== 0) {
            throw new \RuntimeException(implode("\n", $output));
        }

        return $filePath;
    }

    public function restore(string $filePath, ?string $pgBinDir = null): void
    {
        $db = config('database.connections.pgsql');

        putenv('PGPASSWORD=' . $db['password']);

        $pgRestore = $this->findPgRestore($pgBinDir);

        $command = sprintf(
            '%s --host=%s --port=%s --username=%s --no-password --clean --if-exists --dbname=%s %s 2>&1',
            escapeshellarg($pgRestore),
            escapeshellarg($db['host']),
            escapeshellarg($db['port']),
            escapeshellarg($db['username']),
            escapeshellarg($db['database']),
            escapeshellarg($filePath),
        );

        $output = null;
        $exitCode = null;
        exec($command, $output, $exitCode);

        putenv('PGPASSWORD');

        if ($exitCode !== 0) {
            throw new \RuntimeException(implode("\n", $output));
        }
    }

    public function listBackups(string $path): array
    {
        $path = rtrim($path, '\\/');
        if (!is_dir($path)) {
            return [];
        }

        $files = glob($path . DIRECTORY_SEPARATOR . '*.{sql,dump}', GLOB_BRACE);
        if (!$files) {
            return [];
        }

        usort($files, fn($a, $b) => filemtime($b) <=> filemtime($a));

        return array_map(fn($f) => [
            'name' => basename($f),
            'path' => $f,
            'size' => filesize($f),
            'date' => date('Y-m-d H:i:s', filemtime($f)),
        ], $files);
    }

    private function findPgDump(?string $pgBinDir = null): string
    {
        if ($pgBinDir) {
            $explicit = rtrim($pgBinDir, '\\/') . '\\pg_dump.exe';
            if (is_file($explicit)) {
                return $explicit;
            }
        }

        $custom = env('PG_DUMP_PATH');
        if ($custom && is_file($custom)) {
            return $custom;
        }

        exec('where pg_dump 2>nul', $pathOut, $pathCode);
        if ($pathCode === 0 && !empty($pathOut[0])) {
            return $pathOut[0];
        }

        $drives = [];
        exec('wmic logicaldisk get caption 2>nul', $driveOut, $driveCode);
        if ($driveCode === 0) {
            foreach ($driveOut as $line) {
                $line = trim($line);
                if ($line !== '' && $line !== 'Caption' && preg_match('/^([A-Z]):/', $line)) {
                    $drives[] = $line[0] . ':';
                }
            }
        }
        if (empty($drives)) {
            $drives = ['C:', 'D:'];
        }

        $candidates = [];
        foreach ($drives as $drive) {
            $candidates[] = "$drive\\odoo\\PostgreSQL\\bin\\pg_dump.exe";
            $candidates[] = "$drive\\Program Files\\PostgreSQL\\17\\bin\\pg_dump.exe";
            $candidates[] = "$drive\\Program Files\\PostgreSQL\\16\\bin\\pg_dump.exe";
            $candidates[] = "$drive\\Program Files\\PostgreSQL\\15\\bin\\pg_dump.exe";
            $candidates[] = "$drive\\Program Files\\PostgreSQL\\14\\bin\\pg_dump.exe";
            $candidates[] = "$drive\\Program Files\\PostgreSQL\\13\\bin\\pg_dump.exe";
            $candidates[] = "$drive\\Program Files\\PostgreSQL\\12\\bin\\pg_dump.exe";
            $candidates[] = "$drive\\Program Files\\PostgreSQL\\11\\bin\\pg_dump.exe";
            $candidates[] = "$drive\\Program Files\\PostgreSQL\\10\\bin\\pg_dump.exe";
            $candidates[] = "$drive\\Program Files (x86)\\PostgreSQL\\17\\bin\\pg_dump.exe";
            $candidates[] = "$drive\\Program Files (x86)\\PostgreSQL\\16\\bin\\pg_dump.exe";
            $candidates[] = "$drive\\Program Files (x86)\\PostgreSQL\\15\\bin\\pg_dump.exe";
            $candidates[] = "$drive\\Program Files (x86)\\PostgreSQL\\14\\bin\\pg_dump.exe";
            $candidates[] = "$drive\\Program Files (x86)\\PostgreSQL\\13\\bin\\pg_dump.exe";
            $candidates[] = "$drive\\Program Files (x86)\\PostgreSQL\\12\\bin\\pg_dump.exe";
        }

        foreach ($candidates as $p) {
            if (is_file($p)) {
                return $p;
            }
        }

        $service = @shell_exec('sc qc PostgreSQL_For_Odoo 2>nul');
        if ($service && preg_match('/BINARY_PATH_NAME\s+:\s+"(.+?pg_ctl\.exe)"/i', $service, $m)) {
            $binDir = dirname($m[1]);
            $pgDumpPath = $binDir . DIRECTORY_SEPARATOR . 'pg_dump.exe';
            if (is_file($pgDumpPath)) {
                return $pgDumpPath;
            }
        }

        throw new \RuntimeException(
            'pg_dump not found. Set PG_DUMP_PATH in .env with the full path to pg_dump.exe'
        );
    }

    private function findPgRestore(?string $pgBinDir = null): string
    {
        if ($pgBinDir) {
            $explicit = rtrim($pgBinDir, '\\/') . '\\pg_restore.exe';
            if (is_file($explicit)) {
                return $explicit;
            }
        }

        $custom = env('PG_DUMP_PATH');
        if ($custom) {
            $candidate = dirname($custom) . '\\pg_restore.exe';
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        exec('where pg_restore 2>nul', $pathOut, $pathCode);
        if ($pathCode === 0 && !empty($pathOut[0])) {
            return $pathOut[0];
        }

        $drives = [];
        exec('wmic logicaldisk get caption 2>nul', $driveOut, $driveCode);
        if ($driveCode === 0) {
            foreach ($driveOut as $line) {
                $line = trim($line);
                if ($line !== '' && $line !== 'Caption' && preg_match('/^([A-Z]):/', $line)) {
                    $drives[] = $line[0] . ':';
                }
            }
        }
        if (empty($drives)) {
            $drives = ['C:', 'D:'];
        }

        $candidates = [];
        foreach ($drives as $drive) {
            $candidates[] = "$drive\\odoo\\PostgreSQL\\bin\\pg_restore.exe";
            $candidates[] = "$drive\\Program Files\\PostgreSQL\\17\\bin\\pg_restore.exe";
            $candidates[] = "$drive\\Program Files\\PostgreSQL\\16\\bin\\pg_restore.exe";
            $candidates[] = "$drive\\Program Files\\PostgreSQL\\15\\bin\\pg_restore.exe";
            $candidates[] = "$drive\\Program Files\\PostgreSQL\\14\\bin\\pg_restore.exe";
            $candidates[] = "$drive\\Program Files\\PostgreSQL\\13\\bin\\pg_restore.exe";
            $candidates[] = "$drive\\Program Files\\PostgreSQL\\12\\bin\\pg_restore.exe";
            $candidates[] = "$drive\\Program Files\\PostgreSQL\\11\\bin\\pg_restore.exe";
            $candidates[] = "$drive\\Program Files\\PostgreSQL\\10\\bin\\pg_restore.exe";
        }

        foreach ($candidates as $p) {
            if (is_file($p)) {
                return $p;
            }
        }

        throw new \RuntimeException(
            'pg_restore not found. Set PG_DUMP_PATH in .env with the full path to pg_dump.exe'
        );
    }
}
