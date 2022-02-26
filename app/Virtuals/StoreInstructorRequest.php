<?php

/**
 * @OA\Schema(
 *      title="StoreInstructorRequest",
 *      description="Store Instructor request body data",
 *      type="object",
 *      required={"firstName", "lastName","email","password","description","gender","image","phone","type","degree", "departments"}
 * )
 */

class StoreInstructorRequest
{
    /**
     * @OA\Property(
     *      title="first Name",
     *      description="first name of the new instructor",
     *      example="Ahmed"
     * )
     *
     * @var string
     */
    public $firstName;
    /**
     * @OA\Property(
     *      title="last Name",
     *      description="last name of the new instructor",
     *      example="Mohamed"
     * )
     *
     * @var string
     */
    public $lastName;

    /**
     * @OA\Property(
     *      title="description",
     *      description="Description of the new instructor",
     *      example="This is new instructor's description"
     * )
     *
     * @var string
     */
    public $description;
    /**
     * @OA\Property(
     *      title="email",
     *      description="email of the new instructor",
     *      example="This is new instructor's email"
     * )
     *
     * @var string
     */
    public $email;
    /**
     * @OA\Property(
     *      title="password",
     *      description="password of the new instructor",
     *      example="1515222aa"
     * )
     *
     * @var string
     */
    public $password;
    /**
     * @OA\Property(
     *      title="gender",
     *      description="gender of the new instructor",
     *      example="male"
     * )
     *
     * @var string
     */
    public $gender;
    /**
     * @OA\Property(
     *      title="image",
     *      description="image of the new instructor",
     *      example="image.jpg"
     * )
     *
     * @var string
     */
    public $image;
    /**
     * @OA\Property(
     *      title="phone",
     *      description="phone of the new instructor",
     *      example="02221111111"
     * )
     *
     * @var string
     */
    public $phone;
    /**
     * @OA\Property(
     *      title="type",
     *      description="type of the new instructor",
     *      example="instructor"
     * )
     *
     * @var string
     */
    public $type;
    /**
     * @OA\Property(
     *      title="degree",
     *      description="degree of the new instructor",
     *      example="This is new instructor's degree"
     * )
     *
     * @var string
     */
    public $degree;
    /**
     * @OA\Property(
     *      title="departments",
     *      description="departments of the new instructor",
     *     type="array", @OA\Items(@OA\Property(property="id", type="integer"),),),
     *
     * @var \App\Virtual\Models\Department[]
     */
    public $departments;

}