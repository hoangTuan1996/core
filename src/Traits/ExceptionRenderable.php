<?php

namespace MediciVN\Core\Traits;

use Throwable;
use App\Utilities\ResponseStatus;
use MediciVN\Core\Exceptions\MediciException;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

trait ExceptionRenderable
{
    public function renderExceptionResponse(Throwable $e): mixed
    {
        try {
            // write error to log file
            medici_logger('medici', 'InternalError', [
                'error' => $e->getMessage(),
                'level' => 'error',
            ], [
                'log_user' => false
            ]);

            return match (true) {
                $e instanceof AuthenticationException => $this->errorResponse(ResponseStatus::TOKEN_REMOVED),
                $e instanceof RouteNotFoundException => $this->errorResponse(ResponseStatus::TOKEN_REMOVED),
                $e instanceof NotFoundHttpException => $this->errorResponse(ResponseStatus::ROUTE_NOT_DEFINED),
                $e instanceof MethodNotAllowedHttpException => $this->errorResponse(ResponseStatus::METHOD_NOT_ALLOWED),
                $e instanceof MediciException => $this->errorResponse($e->getCode(), $e->getMessage()),
                config('app.debug') => $this->errorResponse(ResponseStatus::INTERNAL_ERROR, $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]),
                default => $this->errorResponse(ResponseStatus::INTERNAL_ERROR),
            };
        } catch (Throwable $th) {
            throw new MediciException(ResponseStatus::INTERNAL_ERROR, $th->getMessage());
        }
    }
}