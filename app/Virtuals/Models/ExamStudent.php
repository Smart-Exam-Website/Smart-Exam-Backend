<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="ExamStudent",
 *     description="ExamStudent model",
 *     @OA\Xml(
 *         name="ExamStudent"
 *     )
 * )
 */
class ExamStudent
{

    /**
     * @OA\Property(
     *     title="ID",
     *     description="ID",
     *     format="int64",
     *     example=1
     * )
     *
     * @var integer
     */
    private $id;

    /**
     * @OA\Property(
     *      title="student ID",
     *      description="Student id",
     *      format="int64",
     *      example=1
     * )
     *
     * @var integer
     */
    public $student_id;


    /**
     * @OA\Property(
     *     title="Student",
     *     description="student"
     * )
     *
     * @var \App\Virtual\Models\Student
     */
    private $student;

    /**
     * @OA\Property(
     *      title="exam ID",
     *      description="Exam id",
     *      format="int64",
     *      example=1
     * )
     *
     * @var integer
     */
    public $exam_id;


    /**
     * @OA\Property(
     *     title="Exam",
     *     description="Exam"
     * )
     *
     * @var \App\Virtual\Models\Exam
     */
    private $exam;
    /**
     * @OA\Property(
     *      title="totalMark",
     *      description="Total mark of exam",
     *      example="60"
     * )
     *
     * @var integer
     */
    public $totalMark;
}
