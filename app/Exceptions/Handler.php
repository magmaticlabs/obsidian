<?php

namespace MagmaticLabs\Obsidian\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param \Exception $exception
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception               $exception
     *
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Exception $exception)
    {
        if ('api' === $request->segment(1)) {
            return $this->apirender($request, $exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * Render an exception into a JSON-API response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception               $exception
     *
     * @return \Illuminate\Http\Response
     */
    private function apirender($request, Exception $exception): Response
    {
        $statuscode = 500; // Default to a generic server-side error code
        $message = trim($exception->getMessage());
        $code = $exception->getCode();
        $source = null;

        // Set variables in reaction to certain exceptions
        if ($exception instanceof HttpException) {
            $statuscode = $exception->getStatusCode();
        } elseif ($exception instanceof AuthenticationException) {
            $statuscode = 401;
            $message = 'You are not authenticated';
        } elseif ($exception instanceof AuthorizationException) {
            $statuscode = 403;
            $message = 'You do not have permission to perform the requested action on this resource';
        } elseif ($exception instanceof ValidationException) {
            $statuscode = 400;
            $message = array_values($exception->errors())[0][0];
            $source = sprintf('/%s', str_replace('.', '/', array_keys($exception->errors())[0]));
        } elseif ($exception instanceof ModelNotFoundException) {
            $statuscode = 404;
            $message = '';
        }

        $title = Response::$statusTexts[$statuscode];
        if (empty($title)) {
            $title = 'An unexpected error occurred';
        }

        $packet = [
            'code'   => (string) $code,
            'status' => (string) $statuscode,
            'source' => [
                'pointer' => $source,
            ],
            'title'  => $title,
            'detail' => $message,
        ];

        if ($statuscode >= 500) {
            if ('local' === strtolower(env('APP_ENV', 'production'))) {
                $packet['trace'] = $exception->getTrace();
            }
        }

        if (empty($code)) {
            unset($packet['code']);
        }

        if (empty($source)) {
            unset($packet['source']);
        }

        // No data key because having both 'data' and 'errors' is against spec
        return new Response([
            'errors' => [$packet],
            'meta'   => new \stdClass(),
            'links'  => [
                '_self' => $request->fullUrl(),
            ],
        ], $statuscode);
    }
}
