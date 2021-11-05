<?php

/**
 * @OA\Schema(
 *      title="ResetPasswordRequest",
 *      description="Reset password request body data",
 *      type="object",
 *      required={"email","token","password"}
 * )
 */

class ResetPasswordRequest
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
     *      title="token",
     *      description="Reset password url token",
     *      example="64 char string"
     * )
     *
     * @var string
     */
    public $token;
    /**
     * @OA\Property(
     *      title="password",
     *      description="new password",
     *      example="yuiuiui"
     * )
     *
     * @var string
     */
    public $password;
   

}