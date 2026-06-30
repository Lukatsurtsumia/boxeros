<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

/**
 * Dumps the database to storage/app/backups and prunes old copies. Schedule it daily (see
 * routes/console.php). On a deployed server, point the host's off-site backup at storage/app/backups,
 * or use the hosting provider's managed DB backups in addition to this.
 */
class BackupDatabase extends Command
{
    protected $signature = 'boxeros:backup {--keep=7 : How many daily backups to retain}';
    protected $description = 'Back up the database to storage/app/backups (gzip) and prune old backups.';

    public function handle(): int
    {
        $db  = config('database.connections.' . config('database.default'));
        $dir = storage_path('app/backups');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $file = $dir . '/boxeros-' . now()->format('Y-m-d_His') . '.sql.gz';

        $cmd = sprintf(
            'mysqldump --no-tablespaces -h %s -P %s -u %s %s | gzip > %s',
            escapeshellarg((string) $db['host']),
            escapeshellarg((string) ($db['port'] ?? 3306)),
            escapeshellarg((string) $db['username']),
            escapeshellarg((string) $db['database']),
            escapeshellarg($file)
        );

        // MYSQL_PWD keeps the password out of the process list.
        $result = Process::env(['MYSQL_PWD' => (string) $db['password']])->timeout(300)->run(['bash', '-c', $cmd]);

        if (!$result->successful() || !file_exists($file) || filesize($file) === 0) {
            @unlink($file);
            $this->error('Backup failed: ' . trim($result->errorOutput()));
            return self::FAILURE;
        }

        $this->info('Backup written: ' . basename($file) . ' (' . round(filesize($file) / 1024, 1) . ' KB)');

        // Keep only the most recent N.
        $keep  = max(1, (int) $this->option('keep'));
        $files = collect(glob($dir . '/boxeros-*.sql.gz'))->sortDesc()->values();
        $files->slice($keep)->each(fn ($f) => @unlink($f));

        return self::SUCCESS;
    }
}
