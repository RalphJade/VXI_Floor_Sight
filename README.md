# VXI FloorSight - IT Asset Management & Network Mapping

**Enterprise-grade IT asset management and network mapping platform for multi-floor BPO call centers**

![Version](https://img.shields.io/badge/version-1.0.0-blue)
![License](https://img.shields.io/badge/license-proprietary-red)
![PHP](https://img.shields.io/badge/PHP-8.2+-purple)
![Laravel](https://img.shields.io/badge/Laravel-11-orange)

---

## 🎯 Overview

VXI FloorSight is a **comprehensive IT asset management and network mapping web application** designed specifically for 24/7 BPO call center environments. It enables IT teams to:

- 📍 **Visualize floor layouts** with interactive SVG maps showing real-time workstation status
- 🔍 **Search and locate assets** across 12 floors using hostname, IP, agent name, or asset tag
- 👥 **Manage user roles** with three-tier permission system (IT Admin, Technician, Operations Manager)
- 🖥️ **Launch remote sessions** directly to workstations (RDP/VNC)
- 📊 **Track occupancy metrics** and campaign seat allocation in real-time
- 📝 **Maintain audit logs** for compliance and troubleshooting
- 🔐 **Enforce IP whitelisting** to restrict access to corporate network only
- 🔗 **Integrate with Active Directory** for automated computer discovery

The application runs **locally on an edge staging desktop PC** within the internal network, keeping all data isolated from centralized remote servers and external networks.

---

## ✨ Key Features

### 🗺️ Interactive Floor Mapping
- Real-time SVG-based floor plans with clickable workstations
- Color-coded status visualization:
  - 🟢 **Green** = Active/Connected
  - 🔴 **Red (Pulsing)** = Offline/Down
  - ⚫ **Gray** = Empty/Available
- 12-floor building with 4 bays per floor (100+ workstations)
- Zoom and pan support on maps

### 🔍 Global Search Engine
- Lightning-fast search across all workstations
- Match by hostname, IP address, agent name, asset tag, or MAC address
- Auto-navigate to floor and highlight target workstation
- Results limited to user's campaign scope (for Operations Managers)

### 📊 Real-Time Metrics Dashboard
- Floor occupancy percentage
- Active/Offline/Empty seat counts
- Campaign-specific analytics
- Recent activity audit trail

### 🖥️ Remote Desktop Integration
- One-click RDP/VNC session launcher
- Direct connection to target workstation hostname
- Audit trail for remote session initiation
- Permission-based access control

### 👨‍💼 Role-Based Access Control (RBAC)
1. **IT Admin (OIC)**
   - Full CRUD on floors, bays, workstations
   - Manage users and roles
   - View complete audit logs
   - Trigger remote sessions

2. **Desktop IT Technician**
   - Read all maps and workstations
   - Search across entire building
   - Edit workstation metadata
   - Trigger remote sessions
   - View audit logs

3. **Operations Manager**
   - Read-only access to assigned campaign/bay
   - Occupancy metrics for their campaign
   - Search within bay only
   - No edit or remote session capabilities

### 🔐 Security Features
- **IP Whitelisting Middleware** - Restrict to corporate subnets (192.168.x.x, 10.x.x.x, 172.16-31.x.x)
- **CSRF Protection** - Automatic token validation
- **Audit Logging** - Track all modifications with user, IP, action, timestamp, and changes
- **Role-Based Policies** - Model-level authorization gates
- **Password Hashing** - Laravel Bcrypt encryption

### 🔗 Active Directory Integration (Skeleton)
- Pre-configured LDAP service for computer object syncing
- Sequential processing to avoid AD server overload
- Floor/bay-specific organizational unit (OU) mapping
- Automated workstation metadata sync
- Console command for manual/scheduled syncs

---

## 📋 Database Schema

### Core Tables
- **floors** - Building floors (1-12)
- **bays** - Campaign areas (A, B, C, D per floor)
- **workstations** - Individual seats with network info
- **audit_logs** - Change tracking and compliance

### RBAC Tables
- **roles** - User role definitions (IT Admin, Technician, Operations Manager)
- **permissions** - Fine-grained permissions (10 types)
- **role_permission** - Role-permission mappings
- **role_user** - User-role assignments

### Extended Users Table
- `employee_id` - Company identifier
- `assigned_bay_id` - For Operations Manager campaign assignment
- `department`, `phone_extension` - Employee info
- `last_login_at` - Login tracking

---

## 🚀 Quick Start

### Prerequisites
```bash
PHP 8.2+
MySQL 8.0+
Node.js 18+
Composer
```

### Installation
```bash
# 1. Clone/navigate to project
cd c:\xampp-new\htdocs\vxi_floor_map

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Configure database in .env
# DB_DATABASE=vxi_floor_map
# DB_USERNAME=root

# 5. Create database
mysql -u root -p -e "CREATE DATABASE vxi_floor_map CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 6. Run migrations and seed
php artisan migrate
php artisan db:seed --class=VxiFloorSightSeeder

# 7. Build assets
npm run build

# 8. Start server
php artisan serve --host=192.168.x.x --port=8000
```

### Default Login Credentials
```
IT Admin:           it_admin@vxi.local / VXI@FloorSight2024
Technician 1:       technician_1@vxi.local / VXI@FloorSight2024
Technician 2:       technician_2@vxi.local / VXI@FloorSight2024
Ops Manager F1:     ops_manager_f1@vxi.local / VXI@FloorSight2024
Ops Manager F2:     ops_manager_f2@vxi.local / VXI@FloorSight2024
```

See [SETUP_GUIDE.md](SETUP_GUIDE.md) for detailed installation instructions.

---

## 📁 Project Structure

```
app/
├── Console/Commands/
│   └── SyncLdapComputers.php          # LDAP sync Artisan command
├── Http/
│   ├── Controllers/
│   │   └── DashboardController.php     # Main dashboard logic
│   ├── Middleware/
│   │   └── IpWhitelist.php             # IP filtering middleware
│   └── Policies/
│       └── WorkstationPolicy.php       # Authorization policies
├── Models/
│   ├── User.php (extended)             # With RBAC relations
│   ├── Floor.php
│   ├── Bay.php
│   ├── Workstation.php
│   ├── AuditLog.php
│   ├── Role.php
│   └── Permission.php
└── Services/
    └── LdapService.php                 # Active Directory integration

database/
├── migrations/
│   ├── 2026_06_16_000001_create_floors_table.php
│   ├── 2026_06_16_000002_create_bays_table.php
│   ├── 2026_06_16_000003_create_workstations_table.php
│   ├── 2026_06_16_000004_create_audit_logs_table.php
│   ├── 2026_06_16_000005_create_rbac_tables.php
│   └── 2026_06_16_000006_extend_users_table.php
└── seeders/
    └── VxiFloorSightSeeder.php         # Sample data generation

resources/
├── css/
│   └── app.css                         # Tailwind styles
└── views/
    └── dashboard/
        └── index.blade.php             # Main dashboard UI

routes/
└── web.php                             # Dashboard routes

config/
└── vxi.php                             # VXI-specific configuration

tests/
├── Feature/                            # Integration tests
└── Unit/                               # Unit tests

SETUP_GUIDE.md                          # Detailed setup instructions
README.md                               # This file
```

---

## 🔌 API Endpoints

### Dashboard
- `GET /dashboard` - Main dashboard view with floor maps

### Workstation Management
- `GET /api/search?term=keyword` - Global search workstations
- `GET /api/workstations/{id}` - Get workstation details
- `PUT /api/workstations/{id}` - Update workstation metadata
- `GET /api/workstations-statuses` - Real-time status polling
- `POST /api/workstations/{id}/remote-session` - Launch RDP/VNC

All endpoints require authentication and respect role-based permissions.

---

## ⚙️ Configuration

### Core Configuration (`config/vxi.php`)
```php
'allowed_subnets' => ['192.168.0.0/16', '10.0.0.0/8', ...]
'remote_protocol' => 'rdp'  // or 'vnc'
'ldap.enabled' => false
'dashboard.status_poll_interval' => 5000  // milliseconds
'monitoring.enabled' => true
```

### Environment Variables (`.env`)
```env
APP_ENV=local
APP_KEY=base64:...
DB_DATABASE=vxi_floor_map
LDAP_ENABLED=false
LDAP_HOSTS=ldap.company.local
LDAP_BASE_DN=dc=company,dc=local
REMOTE_PROTOCOL=rdp
AUDIT_ENABLED=true
```

---

## 🔗 LDAP/Active Directory Integration

VXI FloorSight includes a pre-configured LDAP service for automating computer discovery from Active Directory.

### Setup LDAP
```bash
# 1. Enable LDAP in .env
LDAP_ENABLED=true
LDAP_HOSTS=ldap.company.local
LDAP_BASE_DN=dc=company,dc=local
LDAP_USERNAME=service_account@company.local
LDAP_PASSWORD=YourServiceAccountPassword
```

### Configure Computer OUs (config/vxi.php)
```php
'ldap' => [
    'computer_ous' => [
        'floor_1' => 'ou=Floor1,ou=BPO,dc=company,dc=local',
        'floor_2' => 'ou=Floor2,ou=BPO,dc=company,dc=local',
        // ... configure for all floors
    ],
]
```

### Sync Commands
```bash
# Sync all configured floors
php artisan ldap:sync --all

# Sync specific floor
php artisan ldap:sync --floor=floor_1

# Sync specific computer by hostname
php artisan ldap:sync --hostname=WS-F01-A01
```

---

## 📊 Audit Logging

All workstation modifications are logged with:
- **User** - Who made the change
- **Action** - What was changed (workstation_updated, remote_session_initiated, etc.)
- **Changes** - JSON diff of before/after values
- **IP Address** - Client IP for tracking
- **User Agent** - Browser information
- **Timestamp** - When the action occurred

### Query Audit Logs
```php
// Recent changes in last 24 hours
AuditLog::recent(24)->get();

// Changes by specific user
AuditLog::byUser($userId)->get();

// Remote session initiations
AuditLog::byAction('remote_session_initiated')->get();
```

---

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/DashboardTest.php

# Generate coverage report
php artisan test --coverage
```

---

## 📈 Performance Optimization

1. **Database Indexing** - Optimized queries on frequently searched fields
2. **API Response Caching** - Cache workstation statuses with 5-second TTL
3. **Real-time Updates** - Efficient SVG status polling (5 second interval)
4. **LDAP Rate Limiting** - 100ms delays between computer syncs
5. **Route Caching** - Run `php artisan optimize` for production

---

## 🔒 Security Checklist

- [ ] Configure IP whitelisting subnets in `config/vxi.php`
- [ ] Set strong service account password for LDAP
- [ ] Change default admin credentials after first login
- [ ] Enable HTTPS in production
- [ ] Configure CORS if accessing from different domains
- [ ] Run `php artisan optimize` for production
- [ ] Set appropriate file permissions (755 on directories)
- [ ] Enable audit logging for compliance
- [ ] Regular database backups

---

## 🐛 Troubleshooting

| Issue | Solution |
|-------|----------|
| "Access denied" message | Check IP whitelisting: Is your IP in allowed_subnets? |
| Migrations failed | Ensure MySQL is running and database created |
| SVG not displaying | Check `resources/views/dashboard/index.blade.php` viewBox dimensions |
| Search returns no results | Verify database has data: `php artisan db:seed` |
| Permissions not working | Clear cache: `php artisan config:cache` |
| LDAP connection failed | Test with: `php artisan ldap:sync --all` |

---

## 📖 Documentation

- [SETUP_GUIDE.md](SETUP_GUIDE.md) - Detailed installation and configuration
- [Laravel Documentation](https://laravel.com/docs)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [Eloquent ORM](https://laravel.com/docs/eloquent)

---

## 🚀 Advanced Features

### Custom Remote Protocols
Modify in `resources/views/dashboard/index.blade.php`:
```javascript
// Support for SSH, VNC, RDP
const protocol = data.protocol;
window.location.href = `${protocol}://${data.hostname}`;
```

### Automated Monitoring
Create scheduled command in `app/Console/Kernel.php`:
```php
$schedule->command('ldap:sync --all')
    ->everyFourHours()
    ->onFailure(function () { /* notify */ });
```

### Custom Reporting
Create queries in controller:
```php
$report = Workstation::where('status', 'offline')
    ->with('bay.floor')
    ->orderBy('last_ping_at')
    ->get();
```

---

## 📝 License

Proprietary - VXI Internal Use Only

---

## 👨‍💻 Support

For issues, feature requests, or questions:
1. Check [SETUP_GUIDE.md](SETUP_GUIDE.md)
2. Review error logs: `storage/logs/laravel.log`
3. Verify database: `SELECT * FROM audit_logs LIMIT 5;`
4. Test connectivity: `php artisan tinker` → `Workstation::count()`

---

**VXI FloorSight v1.0.0** | Built with ❤️ for mission-critical BPO operations

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
