<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stancl\Tenancy\Database\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;
use Illuminate\Support\Str;

class CreateTenantCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:create {name} {domain} {--admin-email=} {--admin-password=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new tenant with database and initial setup';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $domain = $this->argument('domain');
        $adminEmail = $this->option('admin-email') ?? 'admin@' . $domain;
        $adminPassword = $this->option('admin-password') ?? Str::random(12);

        $this->info("Creating tenant: {$name}");

        try {
            // Check if domain already exists (idempotency)
            $existingDomain = Domain::where('domain', $domain)->first();
            if ($existingDomain) {
                $this->warn("Domain {$domain} already exists. Skipping creation.");
                return 0;
            }

            // Create tenant
            $tenant = Tenant::create([
                'id' => Str::uuid(),
                'data' => [
                    'name' => $name,
                    'admin_email' => $adminEmail,
                    'admin_password' => $adminPassword,
                    'created_at' => now(),
                    'branding' => [
                        'primary_color' => '#3B82F6',
                        'secondary_color' => '#1E40AF',
                        'logo_url' => null,
                    ],
                    'features' => [
                        'pos' => true,
                        'inventory' => true,
                        'customers' => true,
                        'cash' => true,
                        'reports' => false, // Premium feature
                    ],
                ],
            ]);

            // Create domain
            Domain::create([
                'domain' => $domain,
                'tenant_id' => $tenant->id,
            ]);

            // Initialize tenant database with proper context
            tenancy()->initialize($tenant);

            try {
                // Run tenant migrations
                $this->call('migrate', [
                    '--path' => 'database/migrations/tenant',
                    '--force' => true,
                ]);

                // Run tenant seeds
                $this->call('db:seed', [
                    '--class' => 'TenantSeeder',
                    '--force' => true,
                ]);

                $this->info("Tenant database initialized successfully!");

            } finally {
                // Always end tenancy context
                tenancy()->end();
            }

            $this->info("Tenant created successfully!");
            $this->info("Domain: {$domain}");
            $this->info("Admin Email: {$adminEmail}");
            $this->info("Admin Password: {$adminPassword}");
            $this->warn("Please change the admin password on first login!");

        } catch (\Exception $e) {
            $this->error("Failed to create tenant: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return 1;
        }

        return 0;
    }
}
