<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('answers', function (Blueprint $table) {
            $table->unsignedBigInteger('student_id');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->unsignedBigInteger('exam_id');
            $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');
            $table->unsignedBigInteger('question_id');
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
            // $table->unsignedBigInteger('class_id');
            // $table->foreign('class_id')->references('id')->on('classs')->onDelete('cascade');
            $table->unsignedBigInteger('option_id')->nullable();
            $table->foreign('option_id')->references('id')->on('options')->onDelete('cascade');
            $table->string('studentAnswer')->nullable();
            $table->float('questionMark')->default(0);
            $table->primary(['exam_id', 'student_id', 'question_id'], 'id');
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
        Schema::dropIfExists('answers');
    }
}
