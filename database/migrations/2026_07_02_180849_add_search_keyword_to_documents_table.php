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
        Schema::table('documents', function (Blueprint $table) {
            $table->string('search_keyword')->nullable()->after('abstract_description');
            $table->index(['doc_type_code', 'search_keyword']); // For fast grouped queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['doc_type_code', 'search_keyword']);
            $table->dropColumn('search_keyword');
        });
    }
};
