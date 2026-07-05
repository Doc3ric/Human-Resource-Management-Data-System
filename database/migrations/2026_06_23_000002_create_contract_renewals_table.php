<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_renewals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plantilla_record_id')->constrained('plantilla_records')->cascadeOnDelete();
            $table->date('contract_start_date');
            $table->date('contract_end_date');
            $table->decimal('rate', 12, 2)->nullable();
            $table->enum('rate_type', ['Daily', 'Monthly', 'Annual'])->nullable();
            $table->foreignId('renewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('contract_end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_renewals');
    }
};
