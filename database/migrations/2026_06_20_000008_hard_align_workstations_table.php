<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // workstations spec:
        // id, floor_id FK (cascade), name, type(enum agent/support/om), hostname,
        // ip, mac nullable, status(enum active/alert/empty default empty),
        // agent default Unassigned Station, x int default 100, y int default 100, timestamps

        // 1) Add floor_id column (nullable temporarily)
        Schema::table('workstations', function (Blueprint $table) {
            if (!Schema::hasColumn('workstations', 'floor_id')) {
                $table->unsignedBigInteger('floor_id')->nullable()->after('id');
            }
        });

        // 2) Best-effort mapping from bay_id -> floor_id (only if bay_id still exists)
        if (Schema::hasColumn('workstations', 'bay_id')) {
            DB::statement('UPDATE workstations
                SET floor_id = (SELECT floor_id FROM bays WHERE bays.id = workstations.bay_id)
                WHERE floor_id IS NULL');
        }


        // 3) Remove old Bay FK columns/constraints safely (drop bay_id)
        //    First drop foreign key if exists, then drop bay_id
        Schema::table('workstations', function (Blueprint $table) {
            if (Schema::hasColumn('workstations', 'bay_id')) {
                // MySQL may refuse dropping bay_id if the foreign key/index still exists.
                // Drop FK constraint explicitly when possible.
                try {
                    // Common Laravel FK naming convention.
                    $table->dropForeign(['bay_id']);
                } catch (\Throwable $e) {
                    // ignore and attempt drop column anyway
                }

                // Also attempt dropping the unique index if present.
                try {
                    $table->dropIndex('workstations_bay_id_index');
                } catch (\Throwable $e) {
                    // ignore
                }

                $table->dropColumn('bay_id');
            }
        });


        // 4) Rename old columns into spec names
        Schema::table('workstations', function (Blueprint $table) {
            // name
            if (!Schema::hasColumn('workstations', 'name')) {
                $table->string('name')->after('floor_id');
            }
            // hostname already exists

            // ip/mac rename
            if (!Schema::hasColumn('workstations', 'ip')) {
                $table->string('ip')->nullable()->after('hostname');
            }
            if (!Schema::hasColumn('workstations', 'mac')) {
                $table->string('mac')->nullable()->after('ip');
            }
            // agent
            if (!Schema::hasColumn('workstations', 'agent')) {
                $table->string('agent')->nullable()->after('status');
            }

            if (!Schema::hasColumn('workstations', 'x')) {
                $table->integer('x')->default(100)->after('agent');
            }
            if (!Schema::hasColumn('workstations', 'y')) {
                $table->integer('y')->default(100)->after('x');
            }

            // status enum align: active/alert/empty default empty
            // Use a raw SQL to avoid Laravel/PDO interpreting enum truncation as a warning.
            if (DB::getDriverName() !== 'sqlite') {
                DB::statement("ALTER TABLE workstations MODIFY status ENUM('active','alert','empty') NOT NULL DEFAULT 'empty'");
            }


            // type enum should already match agent/support/om; enforce.
            $table->enum('type', ['agent', 'support', 'om'])->default('agent')->change();

            // defaults
            if (Schema::hasColumn('workstations', 'agent')) {
                $table->string('agent')->default('Unassigned Station')->change();
            }

            // Set floor_id non-null + FK cascade
            $table->unsignedBigInteger('floor_id')->nullable(false)->change();
            $table->foreign('floor_id')->references('id')->on('floors')->onDelete('cascade');
        });

        // Normalize ALL legacy status values BEFORE changing enum definition.
        // Target enum: active, alert, empty.
        DB::statement("UPDATE workstations SET status = 'empty' WHERE status NOT IN ('active','alert','empty')");


        // Best-effort mapping from old columns
        // old: station_id -> name, ip_address -> ip, mac_address -> mac
        DB::statement('UPDATE workstations SET name = station_id WHERE (name IS NULL OR name = \'\') AND station_id IS NOT NULL');
        DB::statement('UPDATE workstations SET ip = ip_address WHERE (ip IS NULL OR ip = \'\') AND ip_address IS NOT NULL');
        DB::statement('UPDATE workstations SET mac = mac_address WHERE (mac IS NULL OR mac = \'\') AND mac_address IS NOT NULL');


        // Drop old columns to match spec
        Schema::table('workstations', function (Blueprint $table) {
            if (Schema::hasColumn('workstations', 'station_id')) {
                $table->dropUnique('workstations_station_id_unique');
            }
            if (Schema::hasColumn('workstations', 'ip_address')) {
                $table->dropUnique('workstations_ip_address_unique');
                $table->dropIndex('workstations_ip_address_index');
            }
            foreach (['station_id', 'ip_address', 'mac_address', 'voice_vlan', 'data_vlan', 'headset_serial', 'agent_name', 'asset_tag', 'last_ping_at', 'last_sync_at', 'notes'] as $col) {
                if (Schema::hasColumn('workstations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    public function down(): void
    {
        // Conservative rollback: re-add bay_id and restore columns as nullable.
        Schema::table('workstations', function (Blueprint $table) {
            if (!Schema::hasColumn('workstations', 'bay_id')) {
                $table->unsignedBigInteger('bay_id')->nullable();
            }

            foreach (['station_id','ip_address','mac_address'] as $col) {
                if (!Schema::hasColumn('workstations', $col)) {
                    $table->string($col)->nullable();
                }
            }
        });
    }
};

