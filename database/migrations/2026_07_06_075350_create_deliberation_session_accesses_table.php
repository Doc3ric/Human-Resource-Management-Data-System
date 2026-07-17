<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliberation_session_accesses', function (Blueprint $table) {
            $table->id();
            $table->string('position_applied');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role_in_session', 30);
            $table->foreignId('granted_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('granted_at')->useCurrent();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revoked_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['position_applied', 'user_id', 'role_in_session'], 'uq_session_access');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliberation_session_accesses');
    }
};
