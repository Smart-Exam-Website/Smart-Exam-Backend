<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAcademicInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('academic_info', function (Blueprint $table) {
            $table->id();
            $table->string('department');
            $table->string('school');
            $table->timestamps();
        });
        Schema::create('academic_info_instructor', function (Blueprint $table) {
            $table->unsignedBigInteger('academic_info_id');
            $table->unsignedBigInteger('instructor_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('academic_info');
        Schema::dropIfExists('academicinfo_instructor');
    }
}
