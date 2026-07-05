<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Module 7 — Photo Management enforcement fields. */
    public function up(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->string('photo_source')->nullable()->after('photo_url'); // manual_upload|201_import
            $table->timestamp('photo_uploaded_at')->nullable()->after('photo_source');
            $table->boolean('photo_confirmed')->default(false)->after('photo_uploaded_at');
        });
    }

    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn(['photo_source', 'photo_uploaded_at', 'photo_confirmed']);
        });
    }
};
