<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('workstations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bay_id')->constrained()->onDelete('cascade');
            $table->string('station_id')->unique();
            $table->string('hostname');
            $table->string('ip_address')->unique();
            $table->string('mac_address')->nullable();
            $table->string('type')->default('agent');
            $table->integer('x')->default(0);
            $table->integer('y')->default(0);
            $table->enum('status', ['active', 'offline', 'empty'])->default('empty');
            $table->string('voice_vlan')->nullable();
            $table->string('data_vlan')->nullable();
            $table->string('headset_serial')->nullable();
            $table->string('agent_name')->nullable();
            $table->string('asset_tag')->nullable();
            $table->timestamp('last_ping_at')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('hostname');
            $table->index('ip_address');
            $table->index('status');
            $table->index('bay_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workstations');
    }
};
