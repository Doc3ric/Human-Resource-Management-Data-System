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
        Schema::create('detail_orders', function (Blueprint $table) {
            $table->id('detail_id');
            $table->foreignId('plantilla_record_id')->constrained('plantilla_records')->restrictOnDelete();

            // Plain strings rather than an organizational_units FK — matches the
            // existing office_department/detailed_unit convention already on
            // plantilla_records, since office names there aren't normalized to
            // organizational_units.id either.
            $table->string('home_unit')->nullable();
            $table->string('detailed_unit');

            $table->string('detail_order_no');
            $table->date('date_issued');
            $table->date('date_effective_start');
            $table->date('date_effective_end')->nullable(); // computed as start + 365 days if left open-ended

            // The detail order document itself (scanned copy), optional at creation time.
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();

            $table->enum('status', ['Active', 'Nearing Expiry', 'Overdue', 'Recalled', 'Extended'])->default('Active');

            // Auto-drafted recall letter once the 1-year mark is reached, pending HRMO Division Head review.
            $table->foreignId('recall_letter_id')->nullable()->constrained('documents')->nullOnDelete();

            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('date_effective_end');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_orders');
    }
};
