<?php

/**
 * @OA\Schema(
 *      title="ChangePasswordRequest",
 *      description="change password request body data",
 *      type="object",
 *      required={"currentPassword","newPassword"}
 * )
 */

class ChangePasswordRequest
{
    /**
     * @OA\Property(
     *      title="currentPassword",
     *      description="User Current Password",
     *      example="12345678"
     * )
     *
     * @var string
     */
    public $currentPassword;

    /**
     * @OA\Property(
     *      title="newPassword",
     *      description="User New Password",
     *      example="Aaa45678"
     * )
     *
     * @var string
     */
    public $newPassword;
}
