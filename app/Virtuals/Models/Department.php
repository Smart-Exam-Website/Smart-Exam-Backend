<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="Department",
 *     description="Department model",
 *     @OA\Xml(
 *         name="Department"
 *     )
 * )
 */
class Department
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
     *      title="name",
     *      description="name of department",
     *      example="Science department"
     * )
     *
     * @var string
     */
    public $name;



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
     *      title="School ID",
     *      description="School id of instructor or student",
     *      format="int64",
     *      example=1
     * )
     *
     * @var integer
     */
    public $school_id;


    /**
     * @OA\Property(
     *     title="School",
     *     description="Department's School"
     * )
     *
     * @var \App\Virtual\Models\School
     */
    private $school;
}
