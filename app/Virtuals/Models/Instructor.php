<?php
namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="Instructor",
 *     description="Instructor model",
 *     @OA\Xml(
 *         name="Instructor"
 *     )
 * )
 */
class Instructor
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
     *      title="degree",
     *      description="degree of instructor",
     *      example="PhD"
     * )
     *
     * @var string
     */
    public $degree;

    /**
     * @OA\Property(
     *      title="verified",
     *      description="verified Instructor",
     *      example="true"
     * )
     *
     * @var bool
     */
    public $verified;

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
     *     title="Deleted at",
     *     description="Deleted at",
     *     example="2020-01-27 17:50:45",
     *     format="datetime",
     *     type="string"
     * )
     *
     * @var \DateTime
     */
    private $deleted_at;

    /**
     * @OA\Property(
     *      title="user ID",
     *      description="User id of instructor",
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
     *     description="Instructor's user"
     * )
     *
     * @var \App\Virtual\Models\User
     */
    private $user;
    /**
     * @OA\Property(
     *     title="Departments",
     *     description="Instructor's departments"
     * )
     *
     * @var \App\Virtual\Models\Department
     */
    private $departments;
}