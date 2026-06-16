<?php

namespace App\Console\Commands;

use App\Services\LdapService;
use Illuminate\Console\Command;

class SyncLdapComputers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ldap:sync {--floor= : Specific floor to sync (e.g., floor_1)}
                                         {--all : Sync all configured floors}
                                         {--hostname= : Sync specific computer by hostname}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync computer objects from Active Directory to workstations table';

    /**
     * Execute the console command.
     */
    public function handle(LdapService $ldapService): int
    {
        if (!config('vxi.ldap.enabled')) {
            $this->error('LDAP is disabled. Enable it in config/vxi.php');
            return 1;
        }

        // Check LDAP connectivity
        if (!$ldapService->isConnected()) {
            $this->error('Cannot connect to LDAP server. Check configuration.');
            return 1;
        }

        $this->info('Connected to LDAP server.');

        // Sync specific hostname
        if ($this->option('hostname')) {
            return $this->syncHostname($ldapService);
        }

        // Sync all floors
        if ($this->option('all')) {
            return $this->syncAllFloors($ldapService);
        }

        // Sync specific floor
        if ($this->option('floor')) {
            return $this->syncFloor($ldapService);
        }

        // Default: show options
        $this->info('Usage:');
        $this->line('  php artisan ldap:sync --all');
        $this->line('  php artisan ldap:sync --floor=floor_1');
        $this->line('  php artisan ldap:sync --hostname=WS-F01-A01');

        return 0;
    }

    /**
     * Sync all configured floors.
     */
    protected function syncAllFloors(LdapService $ldapService): int
    {
        $this->info('Syncing all floors...');

        $allResults = $ldapService->syncAllFloors();

        foreach ($allResults as $floor => $results) {
            if (isset($results['error'])) {
                $this->error("  {$floor}: {$results['error']}");
            } else {
                $this->info("  {$floor}: Synced: {$results['synced']}, Updated: {$results['updated']}, Failed: {$results['failed']}");
            }
        }

        $this->info('LDAP sync completed for all floors.');
        return 0;
    }

    /**
     * Sync specific floor.
     */
    protected function syncFloor(LdapService $ldapService): int
    {
        $floor = $this->option('floor');
        $ous = config('vxi.ldap.computer_ous', []);

        if (!isset($ous[$floor])) {
            $this->error("Floor '{$floor}' not found in configuration.");
            $this->info('Available floors: ' . implode(', ', array_keys($ous)));
            return 1;
        }

        $this->info("Syncing {$floor}...");

        $results = $ldapService->syncComputersFromOU($ous[$floor]);

        if (isset($results['error'])) {
            $this->error("Error: {$results['error']}");
            return 1;
        }

        $this->info("Synced: {$results['synced']}");
        $this->info("Updated: {$results['updated']}");
        $this->info("Failed: {$results['failed']}");

        return 0;
    }

    /**
     * Sync specific hostname.
     */
    protected function syncHostname(LdapService $ldapService): int
    {
        $hostname = $this->option('hostname');

        $this->info("Syncing {$hostname}...");

        $result = $ldapService->syncComputerByHostname($hostname);

        if (isset($result['error'])) {
            $this->error("Error: {$result['error']}");
            return 1;
        }

        $this->info("Status: {$result['status']}");

        if (isset($result['timestamp'])) {
            $this->info("Timestamp: {$result['timestamp']}");
        }

        return 0;
    }
}
