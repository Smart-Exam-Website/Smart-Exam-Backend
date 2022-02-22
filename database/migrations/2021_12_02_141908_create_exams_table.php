<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->dateTime('startAt');
            $table->dateTime('endAt');
            $table->time('duration');
            $table->integer('numberOfTrials');
            $table->integer('totalMark');
            $table->string('examSubject');
            $table->boolean('isPublished')->default(false);
            $table->unsignedBigInteger('instructor_id');
            $table->foreign('instructor_id')->references('id')->on('instructors')->onDelete('cascade');
            $table->timestamps();
        });
        Schema::create('exam_question', function (Blueprint $table) {
            $table->unsignedBigInteger('exam_id');
            $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');
            $table->unsignedBigInteger('question_id');
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
            $table->time('time')->default(0);
            $table->float('mark')->default(0);
            $table->timestamps();
        });
        Schema::create('examSession', function (Blueprint $table) {
            $table->unsignedBigInteger('exam_id');
            $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');
            $table->unsignedBigInteger('student_id');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->dateTime('startTime');
            $table->integer('numberOfFaces')->default(0);
            $table->boolean('isVerified')->default(false);
            $table->integer('attempt')->default(1);
            $table->boolean('isSubmitted')->default(false);
            $table->primary(['exam_id', 'student_id', 'attempt'], 'id');
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
        Schema::dropIfExists('exams');
        Schema::dropIfExists('exam_question');
    }
}
