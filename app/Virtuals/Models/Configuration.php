<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="Configuration",
 *     description="Exam Configuration model",
 *     @OA\Xml(
 *         name="Configurations"
 *     )
 * )
 */
class Configuration
{

    /**
     * @OA\Property(
     *     title="examId",
     *     description="ID",
     *     format="int64",
     *     example=1
     * )
     *
     * @var integer
     */
    private $exam_id;

    /**
     * @OA\Property(
     *      title="Face Recognition",
     *      example="true"
     * )
     *
     * @var boolean
     */
    public $faceRecognition;
    /**
     * @OA\Property(
     *      title="Face Detection",
     *      example="true"
     * )
     *
     * @var boolean
     */
    public $faceDetection;
    /**
     * @OA\Property(
     *      title="Questions Random Order",
     *      example="true"
     * )
     *
     * @var boolean
     */
    public $questionsRandomOrder;
    /**
     * @OA\Property(
     *      title="plagiarism check",
     *      example="true"
     * )
     *
     * @var boolean
     */
    public $plagiarismCheck;
    /**
     * @OA\Property(
     *      title="disable switching browser",
     *      example="true"
     * )
     *
     * @var boolean
     */
    public $disableSwitchBrowser;
    /**
     * @OA\Property(
     *      title="grading method",
     *      description="grading method",
     *      example="manual"
     * )
     *
     * @var string
     */
    public $gradingMethod;
}
