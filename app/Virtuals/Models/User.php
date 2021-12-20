<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="User",
 *     description="User model",
 *     @OA\Xml(
 *         name="User"
 *     )
 * )
 */
class User
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
     *      title="First Name",
     *      description="First name of User",
     *      example="Laila"
     * )
     *
     * @var string
     */
    public $firstName;
    /**
     * @OA\Property(
     *      title="Email",
     *      description="Email of User",
     *      example="h@ex.com"
     * )
     *
     * @var string
     */
    public $email;
    /**
     * @OA\Property(
     *      title="Last Name",
     *      description="Last name of User",
     *      example="Mohsen"
     * )
     *
     * @var string
     */
    public $lastName;
    /**
     * @OA\Property(
     *      title="Password",
     *      description="Password of User",
     *      example="123456"
     * )
     *
     * @var string
     */
    public $password;
    /**
     * @OA\Property(
     *      title="gender",
     *      description="gender of User",
     *      example="female"
     * )
     *
     * @var string
     */
    public $gender;
    /**
     * @OA\Property(
     *      title="image",
     *      description="image of User",
     *      example="http://pixels.com/4040"
     * )
     *
     * @var string
     */
    public $image;
    /**
     * @OA\Property(
     *      title="type",
     *      description="type of User",
     *      example="Admin"
     * )
     *
     * @var string
     */
    public $type;
    /**
     * @OA\Property(
     *      title="phone",
     *      description="phone of User",
     *      example="012233344455"
     * )
     *
     * @var string
     */
    public $phone;


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
     *     title="Email verified at",
     *     description="Email verified at",
     *     example="2020-01-27 17:50:45",
     *     format="datetime",
     *     type="string"
     * )
     *
     * @var \DateTime
     */
    private $email_verified_at;
}
