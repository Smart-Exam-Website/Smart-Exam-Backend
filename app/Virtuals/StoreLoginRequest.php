<?php

/**
 * @OA\Schema(
 *      title="StoreLoginRequest",
 *      description="Store Login request body data",
 *      type="object",
 *      required={"email","password"}
 * )
 */

class StoreLoginRequest
{

    /**
     * @OA\Property(
     *      title="email",
     *      description="email of the user",
     *      example="omar@example.com"
     * )
     *
     * @var string
     */
    public $email;
    /**
     * @OA\Property(
     *      title="password",
     *      description="password of the user",
     *      example="12345678"
     * )
     *
     * @var string
     */
    public $password;
}
