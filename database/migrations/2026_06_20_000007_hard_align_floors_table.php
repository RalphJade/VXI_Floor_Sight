<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // floors spec:
        // id, name, campaign, subnet(nullable), vlan_a/b/c(nullable), timestamps

        Schema::table('floors', function (Blueprint $table) {
            // Add missing columns (if they don't exist)
            if (!Schema::hasColumn('floors', 'name')) {
                $table->string('name')->after('id');
            }
            if (!Schema::hasColumn('floors', 'campaign')) {
                $table->string('campaign')->nullable()->after('name');
            }

            // Ensure we don't attempt to add/duplicate columns if the migration partially ran.
            // If name/campaign already exist, skip creating them.


            if (!Schema::hasColumn('floors', 'subnet')) {
                $table->string('subnet')->nullable()->after('campaign');
            }
            if (!Schema::hasColumn('floors', 'vlan_a')) {
                $table->string('vlan_a')->nullable()->after('subnet');
            }
            if (!Schema::hasColumn('floors', 'vlan_b')) {
                $table->string('vlan_b')->nullable()->after('vlan_a');
            }
            if (!Schema::hasColumn('floors', 'vlan_c')) {
                $table->string('vlan_c')->nullable()->after('vlan_b');
            }
        });

        // Best-effort data preservation from old columns
        // old: floor_name -> name, description -> campaign
        // Use DB statements to avoid ORM assumptions.
        DB::statement('UPDATE floors SET name = floor_name WHERE (name IS NULL OR name = \'\') AND floor_name IS NOT NULL');
        DB::statement('UPDATE floors SET campaign = description WHERE (campaign IS NULL OR campaign = \'\') AND description IS NOT NULL');

        Schema::table('floors', function (Blueprint $table) {
            // Drop old columns if present
            if (Schema::hasColumn('floors', 'floor_number')) {
                $table->dropUnique('floors_floor_number_unique');
                $table->dropColumn('floor_number');
            }
            if (Schema::hasColumn('floors', 'floor_name')) {
                $table->dropColumn('floor_name');
            }
            if (Schema::hasColumn('floors', 'total_seats')) {
                $table->dropColumn('total_seats');
            }
            if (Schema::hasColumn('floors', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('floors', 'svg_map_path')) {
                $table->dropColumn('svg_map_path');
            }
        });
    }

    public function down(): void
    {
        // Reversing a hard-align is non-trivial because we dropped old columns.
        // Implement a conservative rollback: add back old columns without data guarantee.
        Schema::table('floors', function (Blueprint $table) {
            if (!Schema::hasColumn('floors', 'floor_number')) {
                $table->integer('floor_number')->nullable();
            }
            if (!Schema::hasColumn('floors', 'floor_name')) {
                $table->string('floor_name')->nullable();
            }
            if (!Schema::hasColumn('floors', 'total_seats')) {
                $table->integer('total_seats')->nullable();
            }
            if (!Schema::hasColumn('floors', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('floors', 'svg_map_path')) {
                $table->string('svg_map_path')->nullable();
            }

            // Keep new columns as-is
        });
    }
};

