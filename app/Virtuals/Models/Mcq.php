<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="Mcq",
 *     description="Mcq model",
 *     @OA\Xml(
 *         name="Mcq"
 *     )
 * )
 */
class Mcq
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


    /**
     * @OA\Property(
     *      title="McqAnswer ID",
     *      description="McqAnswer id",
     *      format="int64",
     *      example=8
     * )
     *
     * @var integer
     */
    public $mcqanswer_id;


    /**
     * @OA\Property(
     *     title="McqAnswer",
     *     description="McqAnswer"
     * )
     *
     * @var \App\Virtual\Models\McqAnswer
     */
    private $mcqanswer;
}
