<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepartmentInstructorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('department_instructors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')->references('id')->on('departments');
            $table->unsignedBigInteger('instructor_id');
            $table->foreign('instructor_id')->references('id')->on('instructors');
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
        Schema::dropIfExists('department_instructors');
    }
}
