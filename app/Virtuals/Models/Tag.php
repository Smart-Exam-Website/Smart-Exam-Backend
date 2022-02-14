<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="Tag",
 *     description="Tag model",
 *     @OA\Xml(
 *         name="Tag"
 *     )
 * )
 */
class Tag
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
     *     title="Tag",
     *     description="Tag",
     *     format="int64",
     *     example="Science"
     * )
     *
     * @var string
     */
    private $name;

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
