<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="StudentAnswer",
 *     description="Student answer model",
 *     @OA\Xml(
 *         name="StudentAnswer"
 *     )
 * )
 */
class StudentAnswer
{

    /**
     * @OA\Property(
     *     title="exam id",
     *     description="ID",
     *     format="int64",
     *     example=1
     * )
     *
     * @var integer
     */
    private $exam_id;
    /**
     * @OA\Property(
     *     title="student id",
     *     description="ID",
     *     format="int64",
     *     example=1
     * )
     *
     * @var integer
     */
    private $student_id;
    /**
     * @OA\Property(
     *     title="option id",
     *     description="ID",
     *     format="int64",
     *     example=1
     * )
     *
     * @var integer
     */
    private $option_id;
    /**
     * @OA\Property(
     *     title="question id",
     *     description="ID",
     *     format="int64",
     *     example=1
     * )
     *
     * @var integer
     */
    private $question_id;
    /**
     * @OA\Property(
     *     title="question mark",
     *     description="ID",
     *     format="int64",
     *     example=1
     * )
     *
     * @var float
     */
    private $question_mark;

    /**
     * @OA\Property(
     *      title="student answer",
     *      description="student answer",
     *      example="Science department"
     * )
     *
     * @var string
     */
    public $studentAnswer;
}
