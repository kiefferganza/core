<?php

namespace App\Exceptions;

use App\Exceptions\HttpException as AppHttpException;
use App\Support\Response\ReturnsResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    use ReturnsResponse;

    protected $levels = [
        //
    ];

    protected $dontReport = [
        AppHttpException::class,
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->renderable(function (AuthorizationException $e) {
            if ($e->getCode()) {
                return $this->respondWithError(
                    $e->getCode(),
                    Response::HTTP_FORBIDDEN,
                    $e->getMessage()
                );
            }
        });

        $this->reportable(function (Throwable $e) {
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        });
    }
}
