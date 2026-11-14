<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('criteria', function (Blueprint $table) {
            $table->id();
            // Kode teknis (C1, C2, C3). Tampilan utama memakai kolom name.
            $table->string('code', 10)->unique();
            $table->string('name', 100);
            // Nilai: benefit atau cost. Dipetakan ke Enum CriterionType di aplikasi.
            $table->string('type', 20);
            // Metrik sumber: attendance_rate, on_time_rate, no_early_leave_rate. Tipe terkunci mengikuti metrik.
            $table->string('source_metric', 50)->unique();
            // Persen. Jumlah bobot kriteria aktif harus 100,00 dan dijaga di aplikasi.
            $table->decimal('weight', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('criteria');
    }
};