<?php

/**
 * @OA\Schema(
 *      title="StoreStudentRequest",
 *      description="Store Student request body data",
 *      type="object",
 *      required={"firstName", "lastName","email","password","description","gender","image","phone","type","studentCode", "department"}
 * )
 */

class StoreStudentRequest
{
    /**
     * @OA\Property(
     *      title="first Name",
     *      description="first name of the new student",
     *      example="Laila"
     * )
     *
     * @var string
     */
    public $firstName;
    /**
     * @OA\Property(
     *      title="last Name",
     *      description="last name of the new student",
     *      example="Mohsen"
     * )
     *
     * @var string
     */
    public $lastName;

    /**
     * @OA\Property(
     *      title="email",
     *      description="email of the new student",
     *      example="h@ex.com"
     * )
     *
     * @var string
     */
    public $email;
    /**
     * @OA\Property(
     *      title="password",
     *      description="password of the new student",
     *      example="123456"
     * )
     *
     * @var string
     */
    public $password;
    /**
     * @OA\Property(
     *      title="gender",
     *      description="gender of the new student",
     *      example="female"
     * )
     *
     * @var string
     */
    public $gender;
    /**
     * @OA\Property(
     *      title="image",
     *      description="image of the new student",
     *      example="http://pixels.com/4040"
     * )
     *
     * @var string
     */
    public $image;
    /**
     * @OA\Property(
     *      title="phone",
     *      description="phone of the new student",
     *      example="012233344455"
     * )
     *
     * @var string
     */
    public $phone;
    /**
     * @OA\Property(
     *      title="type",
     *      description="type of the new student",
     *      example="student"
     * )
     *
     * @var string
     */
    public $type;
    /**
     * @OA\Property(
     *      title="studentCode",
     *      description="studentCode of the new student",
     *      example="1702"
     * )
     *
     * @var string
     */
    public $studentCode;
    /**
     * @OA\Property(
     *      title="department",
     *      description="department of the new student",
     *     type="array", @OA\Items(@OA\Property(property="id", type="integer",example="1"),),),
     *
     * @var \App\Virtual\Models\Department
     */
    public $department;
}
