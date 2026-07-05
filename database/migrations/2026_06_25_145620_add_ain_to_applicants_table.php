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
        Schema::table('applicants', function (Blueprint $table) {
            $table->string('ain')->nullable()->unique()->after('reference_no');
        });

        $applicants = \App\Models\Applicant::all();
        foreach ($applicants as $applicant) {
            // Temporarily bypass timestamps if desired, but fine to update updated_at
            $ain = \App\Models\Applicant::generateAin(
                $applicant->date_of_birth,
                $applicant->first_name,
                $applicant->middle_name,
                $applicant->id
            );
            \DB::table('applicants')->where('id', $applicant->id)->update(['ain' => $ain]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn('ain');
        });
    }
};
