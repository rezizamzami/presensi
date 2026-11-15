<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absent_runs', function (Blueprint $table) {
            $table->id();
            // Satu baris per tanggal yang sudah diproses perintah alpa. unique menjaga perintah tetap idempoten.
            $table->date('work_date')->unique();
            $table->timestamp('processed_at');
            $table->unsignedInteger('employee_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absent_runs');
    }
};
