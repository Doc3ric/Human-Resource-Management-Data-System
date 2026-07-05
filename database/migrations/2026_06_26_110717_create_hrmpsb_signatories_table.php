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
        Schema::create('hrmpsb_signatories', function (Blueprint $table) {
            $table->id();
            $table->enum('team', ['legislative', 'management']);
            $table->string('role'); // e.g., 'secretary', 'member_1', 'member_2', 'chairman', 'appointing_authority'
            $table->string('name')->nullable();
            $table->string('title')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hrmpsb_signatories');
    }
};
