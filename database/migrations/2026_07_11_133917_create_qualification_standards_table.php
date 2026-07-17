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
        Schema::create('qualification_standards', function (Blueprint $table) {
            $table->id();
            $table->string('position_title')->unique();
            $table->text('education')->nullable();
            $table->text('training')->nullable();
            $table->text('experience')->nullable();
            $table->text('eligibility')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qualification_standards');
    }
};
