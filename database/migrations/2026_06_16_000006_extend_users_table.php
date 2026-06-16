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
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_id')->nullable()->unique()->after('id');
            $table->foreignId('assigned_bay_id')->nullable()->constrained('bays')->onDelete('set null')->after('email');
            $table->string('department')->nullable()->after('assigned_bay_id');
            $table->string('phone_extension')->nullable()->after('department');
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['assigned_bay_id']);
            $table->dropColumn(['employee_id', 'assigned_bay_id', 'department', 'phone_extension', 'last_login_at']);
        });
    }
};
