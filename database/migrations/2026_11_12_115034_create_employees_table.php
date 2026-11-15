<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            // Pegawai boleh belum punya akun. Satu akun hanya untuk satu pegawai.
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            // Unik termasuk baris yang di-soft-delete, jadi nomor lama tidak bisa dipakai lagi.
            $table->string('employee_number', 20)->unique();
            $table->string('full_name', 100);
            $table->foreignId('department_id')->constrained()->restrictOnDelete();
            $table->foreignId('position_id')->constrained()->restrictOnDelete();
            $table->foreignId('work_schedule_id')->constrained()->restrictOnDelete();
            $table->date('join_date');
            $table->date('end_date')->nullable();
            // Nilai: active atau inactive. Dipetakan ke Enum EmployeeStatus di aplikasi.
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['department_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
