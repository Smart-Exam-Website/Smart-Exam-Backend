<?php

/**
 * @OA\Schema(
 *      title="StoreExamStepOne",
 *      description="Creating an exam, step one: storing its data",
 *      type="object",
 *      required={"name","numberOfTrials","description","totalMark","duration","startAt","endAt","examSubject"}
 * )
 */

class StoreExamStepOne
{
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
     *      title="Exam Subject",
     *      description="Exam Subject",
     *      example="Maths"
     * )
     *
     * @var string
     */
    public $examSubject;

}