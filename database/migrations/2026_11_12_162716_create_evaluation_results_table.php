<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_results', function (Blueprint $table) {
            $table->id();
            // Hasil ikut terhapus bila periodenya dihapus, karena tidak berarti tanpa periode.
            $table->foreignId('evaluation_period_id')->constrained()->cascadeOnDelete();
            // Dilarang hapus permanen pegawai yang sudah punya hasil evaluasi.
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->json('raw_values');
            $table->json('normalized_values');
            // Tiga kolom ini kosong bila pegawai ditandai "data tidak cukup".
            $table->decimal('final_score', 7, 6)->nullable();
            $table->unsignedSmallInteger('ranking')->nullable();
            $table->string('category', 30)->nullable();
            $table->timestamps();

            $table->unique(['evaluation_period_id', 'employee_id']);
            $table->index(['evaluation_period_id', 'ranking']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_results');
    }
};
