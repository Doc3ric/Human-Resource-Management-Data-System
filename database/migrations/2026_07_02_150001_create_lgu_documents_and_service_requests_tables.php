<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Module 10-M5 — LGU Documents (EO/Memo/Ordinance) + service-request tracking. New domain. */
    public function up(): void
    {
        Schema::create('lgu_documents', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20); // EO|MEMO|ORDINANCE
            $table->string('number')->nullable();
            $table->string('title');
            $table->date('date_issued');
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete(); // scanned copy via IDCC
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_type', 30); // COE|SERVICE_RECORD|CERTIFICATION
            $table->string('requester_name');
            $table->string('requester_contact')->nullable();
            $table->unsignedBigInteger('plantilla_record_id')->nullable();
            $table->date('date_requested');
            // R.A. 11032 (Citizen's Charter) — simple transactions: 3 working
            // days; the window here is stored as a concrete due date computed
            // at creation, not recalculated, so the countdown is stable.
            $table->date('due_at');
            $table->string('status', 20)->default('pending'); // pending|escalated|completed
            $table->timestamp('escalated_at')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
        Schema::dropIfExists('lgu_documents');
    }
};
