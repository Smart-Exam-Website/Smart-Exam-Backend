<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="Question",
 *     description="Question model",
 *     @OA\Xml(
 *         name="Question"
 *     )
 * )
 */
class Question
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
     *      title="questionText",
     *      description="questionText",
     *      example="How many days are in the week?"
     * )
     *
     * @var string
     */
    public $questionText;

    /**
     * @OA\Property(
     *      title="type",
     *      description="type",
     *      example="mcq"
     * )
     *
     * @var string
     */
    public $type;
    /**
     * @OA\Property(
     *     title="isHidden",
     *     description="isHidden",
     *     format="int64",
     *     example=1
     * )
     *
     * @var integer
     */
    private $isHidden;


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
     *      title="Instructor ID",
     *      description="Instructor id",
     *      format="int64",
     *      example=4
     * )
     *
     * @var integer
     */
    public $instructor_id;


    /**
     * @OA\Property(
     *     title="instructor",
     *     description="instructor"
     * )
     *
     * @var \App\Virtual\Models\Instructor
     */
    private $instructor;

    /**
     * @OA\Property(
     *      title="Mcq ID",
     *      description="Mcq id",
     *      format="int64",
     *      example=8
     * )
     *
     * @var integer
     */
    public $mcq_id;


    /**
     * @OA\Property(
     *     title="Mcq",
     *     description="Mcq"
     * )
     *
     * @var \App\Virtual\Models\Mcq
     */
    private $mcq;

    /**
     * @OA\Property(
     *      title="Tag ID",
     *      description="Tag id",
     *      format="int64",
     *      example=2
     * )
     *
     * @var integer
     */
    public $tag_id;


    /**
     * @OA\Property(
     *     title="Tag",
     *     description="Tag"
     * )
     *
     * @var \App\Virtual\Models\Tag
     */
    private $tag;
}
