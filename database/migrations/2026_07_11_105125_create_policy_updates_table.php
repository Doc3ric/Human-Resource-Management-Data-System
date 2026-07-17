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
        Schema::create('policy_updates', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('url')->unique();
            $table->string('source')->nullable(); // 'DBM', 'CSC', etc.
            $table->date('published_date')->nullable();
            $table->string('matched_keyword')->nullable(); // e.g., 'Leave', 'RRACS', 'Tranche'
            $table->text('description')->nullable();
            $table->enum('status', ['new', 'read', 'dismissed'])->default('new');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_updates');
    }
};
