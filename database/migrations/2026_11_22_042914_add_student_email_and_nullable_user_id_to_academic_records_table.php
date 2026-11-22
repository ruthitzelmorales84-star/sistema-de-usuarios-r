<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_records', function (Blueprint $table) {
            if (! Schema::hasColumn('academic_records', 'student_email')) {
                $table->string('student_email')->nullable()->after('student_name');
            }

            if (Schema::hasColumn('academic_records', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('academic_records', function (Blueprint $table) {
            if (Schema::hasColumn('academic_records', 'student_email')) {
                $table->dropColumn('student_email');
            }
        });
    }
};