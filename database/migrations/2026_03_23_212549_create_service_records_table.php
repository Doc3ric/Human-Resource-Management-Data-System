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
        Schema::create('service_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plantilla_record_id')->constrained('plantilla_records')->onDelete('cascade');
            $table->date('from_date');
            $table->date('to_date')->nullable(); // Null implies "Present"
            $table->string('designation');
            $table->string('status')->nullable(); // e.g. Permanent, Contractual, Casual
            $table->decimal('salary', 12, 2)->nullable(); // Annual or Monthly salary
            $table->string('station_branch')->nullable();
            $table->string('lwp')->nullable(); // Leave Without Pay format
            $table->date('separation_date')->nullable();
            $table->string('cause')->nullable(); // Cause of separation or details of appointment
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_records');
    }
};
