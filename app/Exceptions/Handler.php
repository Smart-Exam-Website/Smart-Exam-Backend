<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var string[]
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var string[]
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    // public function register()
    // {
    //     $this->reportable(function (NotFoundHttpException $e) {

    //         return response()->json('Not found', 404);
    //     });
    // }
    
    public function render($request,Throwable $e)
    {
        // dd($exception);
        if ($e instanceof AuthenticationException)
            return response()->json(['message' => 'Unauthenticated'], 401);
        else if ($e instanceof ModelNotFoundException)
            return response()->json(['message' =>'Error'], 404);
        else if($e instanceof NotFoundHttpException) 
            return response()->json(['message' =>'Route not found'], 404);
        return response()->json(['message' => $e->getMessage()], 400);
    }
}
