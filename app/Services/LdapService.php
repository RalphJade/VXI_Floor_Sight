<?php

namespace App\Services;

use App\Models\Workstation;
use Illuminate\Support\Facades\Log;

/**
 * LDAP Service for Active Directory Integration
 *
 * This service provides skeleton methods for syncing computer objects and
 * user attributes from Active Directory to the VXI FloorSight database.
 * Configure LDAP settings in config/vxi.php and .env
 *
 * @example
 * $ldapService = new LdapService();
 * $ldapService->syncComputersFromOU('ou=Floor1,ou=BPO,dc=company,dc=local');
 */
class LdapService
{
    protected $config;

    public function __construct()
    {
        $this->config = config('vxi.ldap');
    }

    /**
     * Sync computers from a specific Organizational Unit in Active Directory.
     *
     * This method queries AD for all computer objects in a given OU and updates
     * the workstations table with their information (hostname, IP, MAC address).
     * It processes computers sequentially to avoid overloading the central directory.
     *
     * @param string $ou Full DN of the organizational unit
     * @return array Results including count of synced, updated, and failed records
     *
     * @example
     * $results = $ldapService->syncComputersFromOU('ou=Floor1,ou=BPO,dc=company,dc=local');
     * echo "Synced: {$results['synced']}, Updated: {$results['updated']}, Failed: {$results['failed']}";
     */
    public function syncComputersFromOU(string $ou): array
    {
        if (!$this->config['enabled']) {
            Log::warning('LDAP sync attempted but LDAP is disabled');
            return ['synced' => 0, 'updated' => 0, 'failed' => 0];
        }

        try {
            // Initialize LDAP connection
            $connection = $this->connect();
            if (!$connection) {
                return ['error' => 'Failed to connect to LDAP server'];
            }

            // Query computers in the specific OU
            $computers = $this->queryComputersInOU($connection, $ou);

            $results = [
                'synced' => 0,
                'updated' => 0,
                'failed' => 0,
                'details' => [],
            ];

            // Process each computer sequentially
            foreach ($computers as $computer) {
                $result = $this->processComputerRecord($computer);

                if ($result['status'] === 'synced') {
                    $results['synced']++;
                } elseif ($result['status'] === 'updated') {
                    $results['updated']++;
                } else {
                    $results['failed']++;
                }

                $results['details'][] = $result;

                // Rate limiting: sleep 100ms between each computer to avoid AD strain
                usleep(100000);
            }

            // Close LDAP connection
            ldap_close($connection);

            Log::info("LDAP sync from OU completed", $results);

            return $results;
        } catch (\Exception $e) {
            Log::error('LDAP sync error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Sync a specific computer by hostname.
     *
     * @param string $hostname Computer hostname in AD
     * @return array Result with status and details
     */
    public function syncComputerByHostname(string $hostname): array
    {
        if (!$this->config['enabled']) {
            return ['error' => 'LDAP is disabled'];
        }

        try {
            $connection = $this->connect();
            if (!$connection) {
                return ['error' => 'Failed to connect to LDAP'];
            }

            // Query specific computer
            $filter = "(sAMAccountName={$hostname}$)";
            $attrs = ['displayName', 'description', 'cn', 'operatingSystem'];
            $results = ldap_search($connection, $this->config['base_dn'], $filter, $attrs);

            if (!$results || ldap_count_entries($connection, $results) === 0) {
                return ['status' => 'not_found', 'hostname' => $hostname];
            }

            $computer = ldap_get_entries($connection, $results)[0];
            ldap_close($connection);

            return $this->processComputerRecord($computer);
        } catch (\Exception $e) {
            Log::error("LDAP sync for {$hostname} failed: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get all computers in a specific OU with their attributes.
     *
     * @param resource $connection LDAP connection resource
     * @param string $ou Organizational unit DN
     * @return array Array of computer records
     */
    protected function queryComputersInOU($connection, string $ou): array
    {
        $filter = '(&(objectClass=computer)(cn=WS-*))';
        $attrs = [
            'displayName',
            'cn',
            'description',
            'operatingSystem',
            'operatingSystemVersion',
            'userAccountControl',
        ];

        $results = ldap_search(
            $connection,
            $ou,
            $filter,
            $attrs,
            0,
            0,  // Size limit
            5   // Time limit (seconds)
        );

        if (!$results) {
            Log::warning("LDAP query failed for OU: {$ou}");
            return [];
        }

        return ldap_get_entries($connection, $results) ?? [];
    }

    /**
     * Process a single computer record from AD and update workstation DB.
     *
     * @param array $computerData Computer record from LDAP
     * @return array Processing result
     */
    protected function processComputerRecord(array $computerData): array
    {
        try {
            $hostname = $computerData['cn'][0] ?? null;

            if (!$hostname) {
                return ['status' => 'failed', 'reason' => 'No hostname found'];
            }

            // Try to find matching workstation by hostname
            $workstation = Workstation::where('hostname', $hostname)->first();

            if (!$workstation) {
                // Could create a new workstation, but typically they're created manually
                return ['status' => 'not_matched', 'hostname' => $hostname];
            }

            // Extract additional data from AD
            $description = $computerData['description'][0] ?? '';
            $osVersion = $computerData['operatingsystemversion'][0] ?? '';

            // Check if update is needed
            $changed = false;
            if ($workstation->notes !== $description) {
                $workstation->notes = $description;
                $changed = true;
            }

            if ($changed) {
                $workstation->last_sync_at = now();
                $workstation->save();

                return [
                    'status' => 'updated',
                    'hostname' => $hostname,
                    'timestamp' => now()->toDateTimeString(),
                ];
            }

            return [
                'status' => 'synced',
                'hostname' => $hostname,
                'timestamp' => now()->toDateTimeString(),
            ];
        } catch (\Exception $e) {
            Log::error("Error processing computer record: " . $e->getMessage());
            return ['status' => 'failed', 'reason' => $e->getMessage()];
        }
    }

    /**
     * Establish connection to LDAP server.
     *
     * @return resource|false LDAP connection resource or false on failure
     */
    protected function connect()
    {
        try {
            $hosts = $this->config['hosts'];
            $port = $this->config['port'];

            // Format connection string
            $connection = ldap_connect($hosts[0], $port);

            if (!$connection) {
                Log::error('Failed to connect to LDAP server');
                return false;
            }

            // Set LDAP protocol version
            ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);

            // Bind to LDAP with service account
            $bind = ldap_bind(
                $connection,
                $this->config['username'],
                $this->config['password']
            );

            if (!$bind) {
                Log::error('LDAP bind failed: ' . ldap_error($connection));
                return false;
            }

            return $connection;
        } catch (\Exception $e) {
            Log::error('LDAP connection error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Sync all configured floor OUs sequentially.
     *
     * @return array Combined results from all OU syncs
     */
    public function syncAllFloors(): array
    {
        $results = [];

        foreach ($this->config['computer_ous'] as $floor => $ou) {
            Log::info("Starting sync for {$floor}...");
            $results[$floor] = $this->syncComputersFromOU($ou);

            // Sleep 5 seconds between floor syncs to reduce AD load
            sleep(5);
        }

        return $results;
    }

    /**
     * Get LDAP connection status.
     *
     * @return bool True if LDAP server is reachable
     */
    public function isConnected(): bool
    {
        $connection = $this->connect();

        if ($connection) {
            ldap_close($connection);
            return true;
        }

        return false;
    }
}
