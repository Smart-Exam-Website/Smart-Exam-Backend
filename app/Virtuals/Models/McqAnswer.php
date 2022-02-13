<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="Mcq Answer",
 *     description="McqAnswer model",
 *     @OA\Xml(
 *         name="McqAnswer"
 *     )
 * )
 */
class McqAnswer
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
     *     title="isCorrect",
     *     description="isCorrect",
     *     format="int64",
     *     example=1
     * )
     *
     * @var integer
     */
    private $isCorrect;

    /**
     * @OA\Property(
     *      title="Question ID",
     *      description="Question id",
     *      format="int64",
     *      example=8
     * )
     *
     * @var integer
     */
    public $question_id;


    /**
     * @OA\Property(
     *     title="Question",
     *     description="McqAnswer's Question"
     * )
     *
     * @var \App\Virtual\Models\Question
     */
    private $question;

    /**
     * @OA\Property(
     *     title="Created at",
     *     description="Created at",
     *     example="2020-01-27 17:50:45",
     *     format="datetime",
     *     type="string"
     * )
     *
     * @var \DateTime
     */
    private $created_at;

    /**
     * @OA\Property(
     *     title="Updated at",
     *     description="Updated at",
     *     example="2020-01-27 17:50:45",
     *     format="datetime",
     *     type="string"
     * )
     *
     * @var \DateTime
     */
    private $updated_at;
}
