<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;
use Throwable;

/**
 * Dumps the database to storage/app/backups (gzip) and prunes old copies. Schedule it daily
 * (see routes/console.php). The dump is done natively in PHP/PDO — no mysqldump binary needed,
 * which matters because the Alpine app image ships no MySQL client. On the server, point an
 * off-site backup at storage/app/backups, or also use the host's managed DB backups.
 */
class BackupDatabase extends Command
{
    protected $signature = 'boxeros:backup {--keep=7 : How many daily backups to retain}';
    protected $description = 'Back up the database to storage/app/backups (gzip) and prune old backups.';

    public function handle(): int
    {
        $dir = storage_path('app/backups');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $file = $dir.'/boxeros-'.now()->format('Y-m-d_His').'.sql.gz';

        try {
            $pdo = DB::connection()->getPdo();
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
            if (empty($tables)) {
                $this->error('Backup failed: no tables found.');

                return self::FAILURE;
            }

            $gz = gzopen($file, 'wb9');
            if ($gz === false) {
                $this->error('Backup failed: cannot open '.$file.' for writing.');

                return self::FAILURE;
            }

            gzwrite($gz, '-- BoxerOS database backup — '.now()->toDateTimeString()." UTC\n");
            gzwrite($gz, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n");

            $rowCount = 0;
            foreach ($tables as $table) {
                $create = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
                $ddl = $create['Create Table'] ?? $create['Create View'] ?? null;
                if ($ddl === null) {
                    continue; // skip anything we can't recreate
                }

                gzwrite($gz, "\nDROP TABLE IF EXISTS `{$table}`;\n".$ddl.";\n");

                $stmt = $pdo->query("SELECT * FROM `{$table}`");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $values = array_map(
                        fn ($v) => $v === null ? 'NULL' : $pdo->quote((string) $v),
                        array_values($row)
                    );
                    gzwrite($gz, "INSERT INTO `{$table}` VALUES (".implode(',', $values).");\n");
                    $rowCount++;
                }
            }

            gzwrite($gz, "\nSET FOREIGN_KEY_CHECKS=1;\n");
            gzclose($gz);
        } catch (Throwable $e) {
            @unlink($file);
            $this->error('Backup failed: '.$e->getMessage());

            return self::FAILURE;
        }

        // A real dump of even a near-empty DB is far bigger than an empty gzip (~20 bytes).
        if (! file_exists($file) || filesize($file) < 200) {
            @unlink($file);
            $this->error('Backup failed: output too small — dump produced no data.');

            return self::FAILURE;
        }

        $this->info('Backup written: '.basename($file).' ('.round(filesize($file) / 1024, 1).' KB, '
            .count($tables).' tables, '.$rowCount.' rows)');

        // Keep only the most recent N.
        $keep = max(1, (int) $this->option('keep'));
        collect(glob($dir.'/boxeros-*.sql.gz'))->sortDesc()->values()
            ->slice($keep)->each(fn ($f) => @unlink($f));

        return self::SUCCESS;
    }
}
