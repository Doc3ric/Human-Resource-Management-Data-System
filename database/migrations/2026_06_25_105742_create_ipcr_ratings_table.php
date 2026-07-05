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
        Schema::create('ipcr_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plantilla_record_id')->constrained('plantilla_records')->onDelete('cascade');
            $table->enum('period_type', ['jan-jun', 'jul-dec', 'custom'])->default('jan-jun');
            $table->string('custom_period')->nullable();
            $table->integer('year');
            $table->boolean('target_submitted')->default(false);
            $table->decimal('rating', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ipcr_ratings');
    }
};
