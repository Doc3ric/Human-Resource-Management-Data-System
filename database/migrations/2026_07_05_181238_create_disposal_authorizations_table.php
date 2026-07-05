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
        // Module 1B.4 — keeps hard-delete locked until a signed NAP Form
        // No. 3 (Authority to Dispose) reference + scan is recorded. One row
        // authorizes exactly one disposal of one record; forceDelete checks
        // for a matching row before proceeding rather than ever hard-deleting
        // unconditionally.
        Schema::create('disposal_authorizations', function (Blueprint $table) {
            $table->id();
            $table->string('disposable_type');
            $table->unsignedBigInteger('disposable_id');
            $table->string('nap_form_reference');
            $table->string('file_path')->nullable();
            $table->foreignId('authorized_by')->constrained('users');
            $table->timestamp('authorized_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['disposable_type', 'disposable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disposal_authorizations');
    }
};
