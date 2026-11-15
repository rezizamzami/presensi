<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('late_tolerance_minutes')->default(0);
            // Nomor hari ISO: 1 = Senin sampai 7 = Minggu.
            $table->json('work_days');
            $table->boolean('is_default')->default(false);

            // MySQL tidak punya unique index parsial. Kolom virtual ini bernilai 1 hanya untuk
            // jadwal default dan NULL untuk lainnya, sehingga database menolak dua jadwal default.
            $table->unsignedTinyInteger('default_marker')
                ->nullable()
                ->virtualAs('if(`is_default`, 1, null)')
                ->unique();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_schedules');
    }
};
