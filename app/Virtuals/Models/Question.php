<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="Question",
 *     description="Question model",
 *     @OA\Xml(
 *         name="Question"
 *     )
 * )
 */
class Question
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
     *      title="questionText",
     *      description="questionText",
     *      example="How many days are in the week?"
     * )
     *
     * @var string
     */
    public $questionText;

    /**
     * @OA\Property(
     *      title="type",
     *      description="type",
     *      example="mcq"
     * )
     *
     * @var string
     */
    public $type;


    /**
     * @OA\Property(
     *     title="Created at",
     *     description="Created at",
     *     example="2021-05-22 17:50:45",
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
     *     example="2021-05-22 17:50:45",
     *     format="datetime",
     *     type="string"
     * )
     *
     * @var \DateTime
     */
    private $updated_at;
}
