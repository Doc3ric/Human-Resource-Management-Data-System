<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Module 0.1: tracks whether the user's current password meets the
     * 8-char alphanumeric+special complexity gate. Defaults true for
     * existing accounts (their hashed passwords can't be retroactively
     * checked) — the gate applies going forward whenever a password is set.
     */
    public function up(): void
    {
        if (Schema::hasColumn('users', 'meets_complexity_gate')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('meets_complexity_gate')->default(true)->after('must_change_password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('meets_complexity_gate');
        });
    }
};
