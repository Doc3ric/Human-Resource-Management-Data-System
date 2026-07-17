<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Enhancement Spec Sec. 1 — column_visibility persisted "per user, per
     * tab" as the spec literally requires (the initial localStorage-only
     * implementation was per-browser, not per-account).
     */
    public function up(): void
    {
        Schema::create('user_table_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('tab_key');
            $table->json('hidden_columns')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'tab_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_table_preferences');
    }
};
