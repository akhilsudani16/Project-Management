<?php

use App\Helpers\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Always render JSON responses
        $exceptions->shouldRenderJsonWhen(fn () => true);

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            return ApiResponse::unauthorized($e->getMessage() ?: 'This action is unauthorized.');
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            $model = class_basename($e->getModel());
            $resourceName = strtolower($model);

            return ApiResponse::notFound(__('api.resource_not_found', ['resource' => $resourceName]));
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            // route not found
            return ApiResponse::notFound(__('api.route_not_found'));
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            return ApiResponse::validationError('Validation failed.', $e->errors());
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            logger()->error($e->getMessage(), ['exception' => $e]);

            return ApiResponse::error('An unexpected error occurred.');
        });
    })->create();
