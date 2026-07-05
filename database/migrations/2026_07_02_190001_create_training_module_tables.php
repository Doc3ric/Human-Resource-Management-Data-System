<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Module 10B — Learning & Development (Training). New domain. */
    public function up(): void
    {
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type', 30); // in_house|external|online|seminar|workshop|conference|scholarship|other
            $table->string('competency_area')->nullable();
            $table->string('institution')->nullable();
            $table->string('venue')->nullable();
            $table->date('date_from');
            $table->date('date_to');
            $table->decimal('total_hours', 6, 2);
            $table->string('organizer')->nullable();
            $table->string('fund_source')->nullable();
            $table->string('reference_authority')->nullable();
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('training_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')->constrained('trainings')->cascadeOnDelete();
            $table->unsignedBigInteger('personnel_id')->nullable(); // PGB personnel link
            $table->string('participant_name')->nullable(); // non-PGB ad-hoc
            $table->string('participant_office')->nullable();
            $table->boolean('is_resource_speaker')->default(false);
            $table->string('topic')->nullable(); // for resource speakers
            $table->string('attendance_status', 15)->default('present'); // present|partial|absent|excused
            $table->decimal('hours_attended', 6, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('training_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_participant_id')->constrained('training_participants')->cascadeOnDelete();
            $table->string('certificate_type', 20); // attendance|resource_speaker
            $table->string('reference_no')->unique();
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('issued_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_certificates');
        Schema::dropIfExists('training_participants');
        Schema::dropIfExists('trainings');
    }
};
