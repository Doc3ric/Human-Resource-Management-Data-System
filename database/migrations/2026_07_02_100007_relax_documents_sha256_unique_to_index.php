<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Module 9A.10 needs a "keep both" resolution path — a deliberate,
     * justified second record sharing the same file hash (e.g. the same
     * certificate legitimately attached to two different personnel records).
     * A hard DB-level unique constraint can't allow that, so duplicate
     * detection is enforced at the application layer (DuplicateDetectionService
     * query) instead, and this becomes a plain lookup index.
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropUnique(['sha256_hash']);
            $table->index('sha256_hash');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['sha256_hash']);
            $table->unique('sha256_hash');
        });
    }
};
