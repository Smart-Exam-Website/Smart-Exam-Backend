<?php

/**
 * @OA\Schema(
 *      title="ForgotPasswordRequest",
 *      description="Forgot password request body data",
 *      type="object",
 *      required={"email"}
 * )
 */

class ForgotPasswordRequest
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
   

}