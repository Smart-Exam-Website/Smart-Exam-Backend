<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="Student",
 *     description="Student model",
 *     @OA\Xml(
 *         name="Student"
 *     )
 * )
 */
class Student
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
     *      title="studentCode",
     *      description="studentCode of student",
     *      example="1702"
     * )
     *
     * @var string
     */
    public $studentCode;

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

    /**
     * @OA\Property(
     *      title="user ID",
     *      description="User id of student",
     *      format="int64",
     *      example=1
     * )
     *
     * @var integer
     */
    public $user_id;


    /**
     * @OA\Property(
     *     title="User",
     *     description="Student's user"
     * )
     *
     * @var \App\Virtual\Models\User
     */
    private $user;
    /**
     * @OA\Property(
     *      title="department ID",
     *      description="Department id of student",
     *      format="int64",
     *      example=1
     * )
     *
     * @var integer
     */
    public $department_id;


    /**
     * @OA\Property(
     *     title="Department",
     *     description="Student's department"
     * )
     *
     * @var \App\Virtual\Models\Department
     */
    private $department;
}
