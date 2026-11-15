<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            // Tanggal WIB. Pasangan unik dengan employee_id mencegah absen ganda.
            $table->date('work_date');
            // Disimpan UTC. dateTime dipilih agar tidak terkena batas tahun 2038 milik timestamp.
            $table->dateTime('check_in_at')->nullable();
            $table->dateTime('check_out_at')->nullable();
            $table->unsignedSmallInteger('late_minutes')->default(0);
            $table->unsignedSmallInteger('early_leave_minutes')->default(0);
            // Nilai: present, late, absent, permission, sick. Dipetakan ke Enum AttendanceStatus di aplikasi.
            $table->string('status', 20);
            $table->foreignId('leave_request_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_manual')->default(false);
            $table->text('correction_reason')->nullable();
            $table->foreignId('corrected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['employee_id', 'work_date']);
            $table->index(['work_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
