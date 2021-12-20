<?php

/**
 * @OA\Schema(
 *      title="StoreExamStepFour",
 *      description="Creating an exam, step Two: add questions' marks and time",
 *      type="object",
 *      required={"examId","questions"}
 * )
 */

class StoreExamStepFour
{
    /**
     * @OA\Property(
     *      title="Exam Id",
     *      description="id of Exam",
     *      example="3"
     * )
     *
     * @var integer
     */
    public $examId;
    /**
     * @OA\Property(
     *      title="questions",
     *      description="questions of the new instructor",
     *     type="array", @OA\Items(@OA\Property(property="id", type="integer"), @OA\Property(property="mark", type="float", example= "50"), @OA\Property(property="time", type="time", example = "01:30:00")),),
     *
     * @var \App\Virtual\Models\Question[]
     */
    public $questions;

}