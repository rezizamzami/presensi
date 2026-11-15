<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_periods', function (Blueprint $table) {
            $table->id();
            // Selalu diisi tanggal 1 bulan yang dievaluasi.
            $table->date('month')->unique();
            $table->string('status', 20)->default('draft');
            // Salinan bobot dan pengaturan saat evaluasi dijalankan, agar hasil lama tidak berubah.
            $table->json('weights_snapshot')->nullable();
            $table->json('settings_snapshot')->nullable();
            $table->timestamp('run_at')->nullable();
            // Dikosongkan bila akun dihapus, agar riwayat periode tetap utuh.
            $table->foreignId('run_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('finalized_at')->nullable();
            $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_periods');
    }
};
