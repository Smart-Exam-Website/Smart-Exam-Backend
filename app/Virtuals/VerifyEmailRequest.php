<?php

/**
 * @OA\Schema(
 *      title="VerifyEmailRequest",
 *      description="Email verification request body data",
 *      type="object",
 *      required={"email", "code"}
 * )
 */

class VerifyEmailRequest
{
    /**
     * @OA\Property(
     *      title="email",
     *      description="User email",
     *      example="example@example.com"
     * )
     *
     * @var string
     */
    public $email;
    /**
     * @OA\Property(
     *      title="code",
     *      description="Vericication code",
     *      example="6C3BT5"
     * )
     *
     * @var string
     */
    public $code;
   

}