<?php

namespace Database\Seeders;

use App\Models\Bay;
use App\Models\Floor;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Workstation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VxiFloorSightSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $roles = [
            [
                'name' => 'it_admin',
                'display_name' => 'IT Admin (OIC)',
                'description' => 'Full CRUD privileges over building floor layouts, campaigns, and users',
            ],
            [
                'name' => 'desktop_technician',
                'display_name' => 'Desktop IT Technician',
                'description' => 'Read-access to maps, search capabilities, and edit workstation metadata',
            ],
            [
                'name' => 'operations_manager',
                'display_name' => 'Operations Manager',
                'description' => 'Read-only access to assigned campaign seats and occupancy metrics',
            ],
        ];

        $createdRoles = [];
        foreach ($roles as $roleData) {
            $createdRoles[$roleData['name']] = Role::firstOrCreate(
                ['name' => $roleData['name']],
                [
                    'display_name' => $roleData['display_name'],
                    'description' => $roleData['description'],
                ]
            );
        }

        // Create permissions
        $permissions = [
            ['name' => 'view_dashboard', 'display_name' => 'View Dashboard'],
            ['name' => 'search_workstations', 'display_name' => 'Search Workstations'],
            ['name' => 'view_workstations', 'display_name' => 'View Workstations'],
            ['name' => 'edit_workstations', 'display_name' => 'Edit Workstations'],
            ['name' => 'delete_workstations', 'display_name' => 'Delete Workstations'],
            ['name' => 'manage_users', 'display_name' => 'Manage Users'],
            ['name' => 'manage_floors', 'display_name' => 'Manage Floors'],
            ['name' => 'manage_bays', 'display_name' => 'Manage Bays'],
            ['name' => 'view_audit_logs', 'display_name' => 'View Audit Logs'],
            ['name' => 'remote_session', 'display_name' => 'Trigger Remote Sessions'],
        ];

        $createdPermissions = [];
        foreach ($permissions as $permissionData) {
            $createdPermissions[$permissionData['name']] = Permission::firstOrCreate(
                ['name' => $permissionData['name']],
                ['display_name' => $permissionData['display_name']]
            );
        }

        // Assign permissions to roles
        // IT Admin - all permissions
        $allPermissionIds = collect($createdPermissions)->mapWithKeys(fn($p) => [$p->id => []])->toArray();
        $createdRoles['it_admin']->permissions()->sync($allPermissionIds);

        // Desktop Technician
        $createdRoles['desktop_technician']->permissions()->sync([
            $createdPermissions['view_dashboard']->id,
            $createdPermissions['search_workstations']->id,
            $createdPermissions['view_workstations']->id,
            $createdPermissions['edit_workstations']->id,
            $createdPermissions['remote_session']->id,
            $createdPermissions['view_audit_logs']->id,
        ]);

        // Operations Manager
        $createdRoles['operations_manager']->permissions()->sync([
            $createdPermissions['view_dashboard']->id,
            $createdPermissions['search_workstations']->id,
            $createdPermissions['view_workstations']->id,
        ]);

        // Create floors (12-floor building)
        $floors = [];
        for ($i = 1; $i <= 12; $i++) {
            $floors[$i] = Floor::firstOrCreate(
                ['floor_number' => $i],
                [
                    'floor_name' => "Floor {$i}",
                    'total_seats' => 100,
                    'description' => "BPO Call Center Floor {$i}",
                ]
            );
        }

        // Create bays and workstations for each floor
        foreach ($floors as $floor) {
            // Create 4 bays per floor (A, B, C, D)
            $bayLetters = ['A', 'B', 'C', 'D'];

            foreach ($bayLetters as $bayLetter) {
                $bay = Bay::firstOrCreate(
                    [
                        'floor_id' => $floor->id,
                        'bay_letter' => $bayLetter,
                    ],
                    [
                        'client_campaign_name' => "Campaign-{$floor->floor_number}{$bayLetter}",
                        'seat_count' => 25,
                    ]
                );

                // Create 25 workstations per bay
                for ($station = 1; $station <= 25; $station++) {
                    $stationId = str_pad($station, 2, '0', STR_PAD_LEFT);
                    $hostname = "WS-F{$floor->floor_number}-{$bayLetter}{$stationId}";
                    $ipLastOctet = ($station * 10) + ord($bayLetter);

                    Workstation::firstOrCreate(
                        ['station_id' => "{$bayLetter}{$stationId}"],
                        [
                            'bay_id' => $bay->id,
                            'hostname' => $hostname,
                            'ip_address' => "192.168.1.{$ipLastOctet}",
                            'mac_address' => sprintf(
                                '00:11:22:%02X:%02X:%02X',
                                $floor->floor_number,
                                ord($bayLetter),
                                $station
                            ),
                            'status' => ['active', 'offline', 'empty'][array_rand(['active', 'offline', 'empty'])],
                            'voice_vlan' => "VLAN-{$floor->floor_number}V",
                            'data_vlan' => "VLAN-{$floor->floor_number}D",
                            'headset_serial' => 'HS-' . strtoupper(substr(md5($hostname), 0, 8)),
                        ]
                    );
                }
            }
        }

        // Create sample users
        // IT Admin
        $itAdmin = User::firstOrCreate(
            ['email' => 'it_admin@vxi.local'],
            [
                'name' => 'John Admin',
                'employee_id' => 'EMP001',
                'password' => Hash::make('VXI@FloorSight2024'),
                'department' => 'IT Operations',
                'email_verified_at' => now(),
            ]
        );
        $itAdmin->roles()->syncWithoutDetaching([$createdRoles['it_admin']->id]);

        // Desktop Technician 1
        $technicianOne = User::firstOrCreate(
            ['email' => 'technician_1@vxi.local'],
            [
                'name' => 'Jane Technician',
                'employee_id' => 'EMP002',
                'password' => Hash::make('VXI@FloorSight2024'),
                'department' => 'IT Support',
                'email_verified_at' => now(),
            ]
        );
        $technicianOne->roles()->syncWithoutDetaching([$createdRoles['desktop_technician']->id]);

        // Desktop Technician 2
        $technicianTwo = User::firstOrCreate(
            ['email' => 'technician_2@vxi.local'],
            [
                'name' => 'Mike Support',
                'employee_id' => 'EMP003',
                'password' => Hash::make('VXI@FloorSight2024'),
                'department' => 'IT Support',
                'email_verified_at' => now(),
            ]
        );
        $technicianTwo->roles()->syncWithoutDetaching([$createdRoles['desktop_technician']->id]);

        // Operations Manager 1 - assigned to Floor 1, Bay A
        $opsMgrOne = User::firstOrCreate(
            ['email' => 'ops_manager_f1@vxi.local'],
            [
                'name' => 'Sarah Operations',
                'employee_id' => 'EMP004',
                'password' => Hash::make('VXI@FloorSight2024'),
                'assigned_bay_id' => Bay::where('floor_id', $floors[1]->id)
                    ->where('bay_letter', 'A')
                    ->first()?->id,
                'department' => 'Operations',
                'email_verified_at' => now(),
            ]
        );
        $opsMgrOne->roles()->syncWithoutDetaching([$createdRoles['operations_manager']->id]);

        // Operations Manager 2 - assigned to Floor 2, Bay B
        $opsMgrTwo = User::firstOrCreate(
            ['email' => 'ops_manager_f2@vxi.local'],
            [
                'name' => 'David Manager',
                'employee_id' => 'EMP005',
                'password' => Hash::make('VXI@FloorSight2024'),
                'assigned_bay_id' => Bay::where('floor_id', $floors[2]->id)
                    ->where('bay_letter', 'B')
                    ->first()?->id,
                'department' => 'Operations',
                'email_verified_at' => now(),
            ]
        );
        $opsMgrTwo->roles()->syncWithoutDetaching([$createdRoles['operations_manager']->id]);
    }
}
