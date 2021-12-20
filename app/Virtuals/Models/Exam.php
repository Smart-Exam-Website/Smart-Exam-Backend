<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="Exam",
 *     description="Exam model",
 *     @OA\Xml(
 *         name="Exam"
 *     )
 * )
 */
class Exam
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
     *      title="Name",
     *      description="name of Exam",
     *      example="Exam 1"
     * )
     *
     * @var string
     */
    public $name;
    /**
     * @OA\Property(
     *      title="Description",
     *      description="Description of Exam",
     *      example="This exam is 60 minutes long!"
     * )
     *
     * @var string
     */
    public $description;
    /**
     * @OA\Property(
     *      title="startAt",
     *      description="Start time of exam(DateTime)",
     * format="datetime",
     *      example="2021-10-29 14:28:54"
     * )
     *
     * @var DateTime
     */
    public $startAt;
    /**
     * @OA\Property(
     *      title="endAt",
     *      description="End time of exam",
     * format="datetime",
     *      example="2021-10-29 14:28:54"
     * )
     *
     * @var DateTime
     */
    public $endAt;
    /**
     * @OA\Property(
     *      title="Duration",
     *      description="duration of exam",
     * format="date",
     *      example="01:30:00"
     * )
     *
     * @var Time
     */
    public $duration;
    /**
     * @OA\Property(
     *      title="numberOfTrials",
     *      description="Number of trials of exam",
     *      example="3"
     * )
     *
     * @var integer
     */
    public $numberOfTrials;
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
    /**
     * @OA\Property(
     *      title="examSubject",
     *      description="Subject of Exam",
     *      example="Maths"
     * )
     *
     * @var string
     */
    public $examSubject;
}
