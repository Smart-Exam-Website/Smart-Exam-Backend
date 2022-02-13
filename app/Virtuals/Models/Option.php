<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="Option",
 *     description="Option model",
 *     @OA\Xml(
 *         name="Option"
 *     )
 * )
 */
class Option
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
     *      title="value",
     *      description="value of choice",
     *      example="Science department"
     * )
     *
     * @var string
     */
    public $value;

    /**
     * @OA\Property(
     *      title="type",
     *      description="type of question",
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
