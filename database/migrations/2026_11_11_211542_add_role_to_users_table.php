<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Bertipe string, bukan enum database, agar penambahan peran baru (misalnya Pimpinan)
            // tidak memerlukan migration. Nilai yang sah dijaga oleh Enum PHP UserRole.
            $table->string('role', 20)->default('employee')->after('password');
            $table->boolean('must_change_password')->default(false)->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'must_change_password']);
        });
    }
};