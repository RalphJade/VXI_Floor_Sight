# VXI FloorSight - Complete Setup Guide

## Project Overview

VXI FloorSight is an **IT Asset Management and Network Mapping web application** designed for a 24/7 BPO call center environment spanning a 12-floor building. The application runs locally on an edge staging desktop PC to keep data isolated from centralized remote servers.

### Key Features
- Interactive SVG floor maps with real-time workstation status visualization
- Role-based access control (IT Admin, Desktop Technician, Operations Manager)
- Dynamic search engine for workstations
- Remote desktop integration (RDP/VNC)
- Audit logging for compliance
- IP whitelisting security middleware
- LDAP/Active Directory integration skeleton
- Real-time occupancy metrics

---

## System Requirements

- **PHP**: 8.2 or higher
- **Database**: MySQL 8.0+ or MariaDB 10.4+
- **Node.js**: 18.x or higher (for Tailwind CSS compilation)
- **Composer**: Latest version
- **Local Network Access**: 192.168.x.x, 10.x.x.x, or 172.16-31.x.x ranges

---

## Installation Steps

### Step 1: Clone/Setup Project
```bash
cd c:\xampp-new\htdocs\vxi_floor_map
```

### Step 2: Install PHP Dependencies
```bash
composer install
```

### Step 3: Install Node Dependencies
```bash
npm install
```

### Step 4: Create Environment File
```bash
cp .env.example .env
```

### Step 5: Configure Database
Edit `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vxi_floor_map
DB_USERNAME=root
DB_PASSWORD=
```

Create the database:
```bash
mysql -u root -p -e "CREATE DATABASE vxi_floor_map CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Step 6: Generate Application Key
```bash
php artisan key:generate
```

### Step 7: Run Migrations
```bash
php artisan migrate
```

### Step 8: Seed Initial Data
```bash
php artisan db:seed --class=VxiFloorSightSeeder
```

This creates:
- 3 roles (IT Admin, Desktop Technician, Operations Manager)
- 10 permissions
- 12 floors with 4 bays each (100 workstations total per floor)
- 5 sample users:
  - **IT Admin**: it_admin@vxi.local / VXI@FloorSight2024
  - **Technician 1**: technician_1@vxi.local / VXI@FloorSight2024
  - **Technician 2**: technician_2@vxi.local / VXI@FloorSight2024
  - **Ops Manager Floor 1**: ops_manager_f1@vxi.local / VXI@FloorSight2024
  - **Ops Manager Floor 2**: ops_manager_f2@vxi.local / VXI@FloorSight2024

### Step 9: Build Tailwind CSS
```bash
npm run build
```

Or for development with watch:
```bash
npm run dev
```

### Step 10: Configure Middleware
Edit `app/Http/Kernel.php` to add IP whitelisting:

```php
protected $routeMiddleware = [
    // ... existing middleware
    'ip_whitelist' => \App\Http\Middleware\IpWhitelist::class,
];
```

### Step 11: Create Configuration File
Create `config/vxi.php`:
```bash
# Run this command or create manually
cat > config/vxi.php << 'EOF'
<?php

return [
    'allowed_subnets' => [
        '192.168.0.0/16',      // Internal LAN
        '10.0.0.0/8',          // Private network
        '172.16.0.0/12',       // Private network
        '127.0.0.1',           // Localhost
    ],
    'remote_protocol' => 'rdp', // 'rdp' or 'vnc'
    'ldap' => [
        'hosts' => ['192.168.x.x'],
        'base_dn' => 'dc=company,dc=local',
        'username' => 'service_account@company.local',
        'password' => env('LDAP_PASSWORD'),
    ],
];
EOF
```

### Step 12: Configure CORS (if needed)
Edit `config/cors.php`:
```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_methods' => ['*'],
'allowed_origins' => ['localhost', '127.0.0.1'],
```

### Step 13: Start the Development Server
```bash
php artisan serve --host=192.168.x.x --port=8000
```

Or use XAMPP's Apache directly and access via `http://localhost/vxi_floor_map`

### Step 14: Access the Application
- **URL**: `http://localhost/vxi_floor_map` or `http://192.168.x.x:8000`
- **Login with IT Admin account** to start exploring

---

## Database Schema Overview

### Tables Created

#### 1. **floors**
```sql
- id (Primary Key)
- floor_number (Unique) - 1-12
- floor_name - Display name
- total_seats - Seat count
- description - Notes
- svg_map_path - Path to SVG floor plan
- timestamps
```

#### 2. **bays**
```sql
- id (Primary Key)
- floor_id (Foreign Key)
- bay_letter - A, B, C, D (per floor)
- client_campaign_name - Campaign identifier
- seat_count - Workstations in bay
- notes - Additional info
- timestamps
- Unique: floor_id + bay_letter
```

#### 3. **workstations**
```sql
- id (Primary Key)
- bay_id (Foreign Key)
- station_id (Unique) - e.g., "A01", "B12"
- hostname (Unique)
- ip_address (Unique)
- mac_address
- status (enum: active|offline|empty)
- voice_vlan - VoIP VLAN
- data_vlan - Data VLAN
- headset_serial
- agent_name
- asset_tag
- last_ping_at - Last connectivity check
- last_sync_at - Last LDAP/directory sync
- notes
- timestamps
- Indexes: hostname, ip_address, status, bay_id
```

#### 4. **audit_logs**
```sql
- id (Primary Key)
- user_id (Foreign Key)
- action_performed - Action description
- workstation_id (Foreign Key, nullable)
- affected_model - Model affected (e.g., "Workstation")
- affected_model_id - ID of affected record
- changes (JSON) - Before/after values
- ip_address - Client IP
- user_agent - Browser info
- timestamp - Action time (not auto-updated)
- Indexes: user_id, workstation_id, timestamp
```

#### 5. **roles** & **permissions**
```sql
roles:
- id, name (unique), display_name, description, timestamps

permissions:
- id, name (unique), display_name, description, timestamps

role_permission:
- role_id (FK), permission_id (FK) - Many-to-many

role_user:
- role_id (FK), user_id (FK) - Many-to-many
```

#### 6. **users** (Extended)
```sql
- Standard Lumen fields (id, name, email, password, timestamps)
- employee_id (unique) - Company ID
- assigned_bay_id (FK) - For operations managers
- department
- phone_extension
- last_login_at - Tracking
```

---

## User Roles & Permissions

### 1. IT Admin (OIC)
**Permissions:**
- Full CRUD on floors, bays, workstations
- Manage users and roles
- View audit logs
- Trigger remote sessions
- Access all reports

**Use Case:** Infrastructure team lead managing the entire building

---

### 2. Desktop IT Technician
**Permissions:**
- Read-only floor maps
- Search all workstations
- Edit workstation metadata (agent name, asset tag, status)
- Trigger remote sessions (RDP/VNC)
- View audit logs
- Cannot delete/create core records

**Use Case:** Support staff troubleshooting workstations

---

### 3. Operations Manager
**Permissions:**
- Read-only access to assigned campaign/bay
- Search within their bay only
- View occupancy metrics for their campaign
- Cannot edit workstations or trigger remote sessions

**Use Case:** Campaign manager tracking seat allocation

---

## Application Architecture

### Directory Structure
```
app/
├── Models/
│   ├── User.php (extended with RBAC)
│   ├── Floor.php
│   ├── Bay.php
│   ├── Workstation.php
│   ├── AuditLog.php
│   ├── Role.php
│   ├── Permission.php
│
├── Http/
│   ├── Controllers/
│   │   └── DashboardController.php
│   ├── Middleware/
│   │   └── IpWhitelist.php
│   ├── Policies/
│   │   └── WorkstationPolicy.php
│
database/
├── migrations/ (6 migration files)
├── seeders/
│   └── VxiFloorSightSeeder.php
│
resources/
├── views/
│   └── dashboard/
│       └── index.blade.php
│
routes/
└── web.php (Dashboard routes)
```

---

## API Endpoints

### Dashboard
- `GET /dashboard` - Main dashboard view

### Workstation Management
- `GET /api/search?term=hostname` - Global search
- `GET /api/workstations/{id}` - Get workstation details
- `PUT /api/workstations/{id}` - Update workstation
- `GET /api/workstations-statuses` - Real-time status updates
- `POST /api/workstations/{id}/remote-session` - Launch RDP/VNC

---

## Frontend Components

### Dashboard Layout (3-Column)
1. **Left Panel (380px)**
   - Floor selection
   - Floor metrics (Active/Offline/Empty counts)
   - Occupancy percentage bar
   - Global search box
   - Recent activity log

2. **Center Area (Flexible)**
   - Interactive SVG floor map
   - Clickable workstation seats
   - Color-coded status visualization:
     - 🟢 Green = Active/Connected
     - 🔴 Red (Pulsing) = Offline/Down
     - ⚫ Gray = Empty/Available
   - Zoom and pan support

3. **Right Sidebar (400px, Slide-out)**
   - Workstation details panel
   - IP, hostname, agent name, asset tag
   - Last connectivity status
   - Action buttons (RDP, Edit, etc.)
   - Closes on Escape or close button

---

## Security Features

### 1. IP Whitelisting Middleware
- Restricts access to corporate network ranges
- Blocks external/unauthorized IPs
- Configurable subnets in `config/vxi.php`
- Returns 403 Forbidden for non-whitelisted IPs

### 2. Role-Based Access Control
- Policy-based authorization on models
- Route middleware for authentication
- User-role-permission tri-layer system

### 3. Audit Logging
- All workstation modifications logged
- IP address, user agent tracked
- JSON change tracking
- Queryable by user, action, time range

### 4. CSRF Protection
- Laravel CSRF token validation on all state-changing requests

---

## Advanced Features Setup

### LDAP/Active Directory Integration (Skeleton)

Create `app/Services/LdapService.php`:
```php
<?php

namespace App\Services;

use LdapRecord\Connection;

class LdapService
{
    protected Connection $ldap;

    public function __construct()
    {
        $this->ldap = new Connection([
            'hosts' => explode(',', config('vxi.ldap.hosts')),
            'base_dn' => config('vxi.ldap.base_dn'),
            'username' => config('vxi.ldap.username'),
            'password' => config('vxi.ldap.password'),
        ]);
    }

    /**
     * Sync computers from specific OU to workstations.
     */
    public function syncComputersFromOU(string $ou): void
    {
        $this->ldap->connect();

        $computers = $this->ldap
            ->query()
            ->in($ou)
            ->where('objectclass', '=', 'computer')
            ->get();

        foreach ($computers as $computer) {
            // Update or create workstation records
            // Process: $computer->getAttributes()
        }
    }
}
```

### Remote Desktop Protocol Integration

To enable actual RDP launches, configure in `resources/views/dashboard/index.blade.php`:
```javascript
// Replace the alert with actual protocol launching:
window.location.href = data.url; // Will trigger RDP client on Windows
```

---

## Monitoring & Maintenance

### Health Check Command
Create `app/Console/Commands/PingWorkstations.php`:
```bash
php artisan tinker
> Workstation::chunk(50, function($batch) { foreach($batch as $ws) ping($ws->ip_address); });
```

### Backup Database
```bash
mysqldump -u root -p vxi_floor_map > backup_$(date +%Y%m%d_%H%M%S).sql
```

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| "Access denied" on login | Check IP whitelisting in `config/vxi.php` |
| Migrations failed | Ensure MySQL is running: `mysql -u root -p` |
| SVG not displaying | Check `resources/views/dashboard/index.blade.php` for valid SVG markup |
| Search not working | Verify database has data: `php artisan db:seed --class=VxiFloorSightSeeder` |
| Permissions not working | Clear config cache: `php artisan config:cache` |

---

## Performance Optimization

1. **Database Indexing**
   - Already configured on frequently queried fields
   - Run `php artisan optimize` for route caching

2. **API Response Caching**
   - Add Redis caching for workstation statuses
   - Configure in `.env`: `CACHE_DRIVER=redis`

3. **Frontend Optimization**
   - Real-time updates poll every 5 seconds (configurable)
   - SVG rendering optimized for 1200+ workstations

---

## Next Steps

1. ✅ Complete initial setup (Steps 1-13)
2. ✅ Verify login with sample users
3. ✅ Explore dashboard with IT Admin account
4. 📋 Import real workstation data from CMDB
5. 📋 Configure LDAP sync for agent names
6. 📋 Setup automated network monitoring tools
7. 📋 Configure backup schedules
8. 📋 Deploy to production environment (if needed)

---

## Support & Documentation

- **Laravel Docs**: https://laravel.com/docs
- **Tailwind CSS**: https://tailwindcss.com/docs
- **Alpine.js**: https://alpinejs.dev
- **Eloquent ORM**: https://laravel.com/docs/eloquent

---

**Version**: 1.0.0 | **Last Updated**: June 2026
