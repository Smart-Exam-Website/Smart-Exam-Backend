<?php

/**
 * @OA\Schema(
 *      title="StoreQuestionRequest",
 *      description="Store Question request body data",
 *      type="object",
 *      required={"questionText", "type","mark","answers","correctAnswer"}
 * )
 */

class StoreQuestionRequest
{
    /**
     * @OA\Property(
     *      title="Question Text",
     *      description="question text",
     *      example="How many days are in the week?"
     * )
     *
     * @var string
     */
    public $questionText;
    /**
     * @OA\Property(
     *      title="type",
     *      description="the type of the question",
     *      example="mcq"
     * )
     *
     * @var string
     */
    public $type;

    /**
     * @OA\Property(
     *      title="answers",
     *      description="answers",
     *      @OA\Items(
     *              type="string",
     *              example={"seven","four","two","ten"}
     *          )
     * )
     *
     * @var array
     */
    public $answers;
    /**
     * @OA\Property(
     *      title="correctAnswer",
     *      description="correct Answer",
     *      example="seven"
     * )
     *
     * @var string
     */
    public $correctAnswer;
}
