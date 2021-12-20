<?php

/**
 * @OA\Schema(
 *      title="StoreExamStepTwo",
 *      description="Creating an exam, step Two: add configuration options",
 *      type="object",
 *      required={"examId","faceRecognition","faceDetection","questionsRandomOrder","plagiarismCheck","disableSwitchBrowser","gradingMethod"}
 * )
 */

class StoreExamStepTwo
{
    /**
     * @OA\Property(
     *      title="Exam Id",
     *      description="id of Exam",
     *      example="3"
     * )
     *
     * @var integer
     */
    public $examId;
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