<?php

/**
 * @OA\Schema(
 *      title="StoreAnswerRequest",
 *      description="Store Answer request body data",
 *      type="object",
 *      required={"option_id","question_id","exam_id"}
 * )
 */

class StoreAnswerRequest
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
    public $option_id;
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
    public $question_id;

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
    public $exam_id;
    /**
     * @OA\Property(
     *      title="studentAnswer",
     *      description="student Answer",
     *      example="seven"
     * )
     *
     * @var string
     */
    public $studentAnswer;
}
