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
        Schema::create('hrmpsb_panel_members', function (Blueprint $table) {
            $table->id();
            $table->string('position_applied'); // Maps to Applicant->position_applied
            $table->foreignId('panel_member_id')->nullable()->constrained('panel_members')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->enum('member_role', ['CHAIRPERSON','REGULAR_MEMBER','ALTERNATE','SECRETARY'])->default('REGULAR_MEMBER');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hrmpsb_panel_members');
    }
};
