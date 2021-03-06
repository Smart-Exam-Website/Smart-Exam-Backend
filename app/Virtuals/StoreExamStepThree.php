<?php

/**
 * @OA\Schema(
 *      title="StoreExamStepThree",
 *      description="Creating an exam, step Two: add questions",
 *      type="object",
 *      required={"examId","questions"}
 * )
 */

class StoreExamStepThree
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
     *     type="array", @OA\Items(@OA\Property(property="id", type="integer"),),),
     *
     * @var \App\Virtual\Models\Question[]
     */
    public $questions;

}