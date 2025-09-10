<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stancl\Tenancy\Database\Models\Tenant;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TenantsBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:backup {--compress} {--retention-days=7} {--cold-retention-days=30}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create encrypted backups for all tenant databases';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $compress = $this->option('compress');
        $retentionDays = (int) $this->option('retention-days');
        $coldRetentionDays = (int) $this->option('cold-retention-days');

        $this->info('Starting tenant database backups...');

        $tenants = Tenant::all();
        $backupCount = 0;
        $errors = [];

        foreach ($tenants as $tenant) {
            try {
                $this->info("Backing up tenant: {$tenant->id}");
                
                $backupPath = $this->createTenantBackup($tenant, $compress);
                
                if ($backupPath) {
                    $backupCount++;
                    $this->info("✓ Backup created: {$backupPath}");
                }

            } catch (\Exception $e) {
                $errors[] = "Tenant {$tenant->id}: " . $e->getMessage();
                $this->error("✗ Failed to backup tenant {$tenant->id}: " . $e->getMessage());
            }
        }

        // Cleanup old backups
        $this->cleanupOldBackups($retentionDays, $coldRetentionDays);

        $this->info("Backup completed. {$backupCount} backups created.");

        if (!empty($errors)) {
            $this->error("Errors encountered:");
            foreach ($errors as $error) {
                $this->error("  - {$error}");
            }
            return 1;
        }

        return 0;
    }

    /**
     * Create backup for a specific tenant
     */
    private function createTenantBackup(Tenant $tenant, bool $compress): ?string
    {
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $tenantId = $tenant->id;
        $dbName = "tenant_{$tenantId}";
        
        // Create backup directory
        $backupDir = "backups/tenants/{$tenantId}";
        Storage::makeDirectory($backupDir);

        // Generate backup filename
        $extension = $compress ? 'sql.gz' : 'sql';
        $filename = "{$dbName}_{$timestamp}.{$extension}";
        $backupPath = "{$backupDir}/{$filename}";

        // Get database connection details
        $host = config('database.connections.tenant.host');
        $port = config('database.connections.tenant.port');
        $username = config('database.connections.tenant.username');
        $password = config('database.connections.tenant.password');

        // Build pg_dump command
        $command = sprintf(
            'PGPASSWORD=%s pg_dump -h %s -p %s -U %s -d %s --no-password --verbose --clean --if-exists',
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($dbName)
        );

        if ($compress) {
            $command .= ' | gzip';
        }

        // Execute backup command
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("pg_dump failed with return code: {$returnCode}");
        }

        // Save backup to storage
        $backupContent = implode("\n", $output);
        Storage::put($backupPath, $backupContent);

        // Encrypt backup (optional - requires encryption key)
        if (config('app.backup_encryption_key')) {
            $this->encryptBackup($backupPath);
        }

        return $backupPath;
    }

    /**
     * Encrypt backup file
     */
    private function encryptBackup(string $backupPath): void
    {
        $content = Storage::get($backupPath);
        $encrypted = encrypt($content);
        Storage::put($backupPath . '.enc', $encrypted);
        Storage::delete($backupPath);
    }

    /**
     * Cleanup old backups
     */
    private function cleanupOldBackups(int $retentionDays, int $coldRetentionDays): void
    {
        $this->info('Cleaning up old backups...');

        $hotCutoff = Carbon::now()->subDays($retentionDays);
        $coldCutoff = Carbon::now()->subDays($coldRetentionDays);

        $backupDirs = Storage::directories('backups/tenants');

        foreach ($backupDirs as $backupDir) {
            $files = Storage::files($backupDir);
            
            foreach ($files as $file) {
                $lastModified = Carbon::createFromTimestamp(Storage::lastModified($file));
                
                if ($lastModified->lt($coldCutoff)) {
                    // Move to cold storage or delete
                    Storage::delete($file);
                    $this->info("Deleted old backup: {$file}");
                } elseif ($lastModified->lt($hotCutoff)) {
                    // Move to cold storage (implement if needed)
                    $this->info("Backup eligible for cold storage: {$file}");
                }
            }
        }
    }
}
