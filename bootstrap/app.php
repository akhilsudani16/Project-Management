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

$isApiRequest = function (Request $request): bool {
    return $request->is('api/*') || $request->expectsJson();
};

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
    ->withExceptions(function (Exceptions $exceptions) use ($isApiRequest): void {
        $exceptions->shouldRenderJsonWhen($isApiRequest);

        $exceptions->render(function (AuthorizationException $e, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return ApiResponse::unauthorized($e->getMessage() ?: 'This action is unauthorized.');
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            $model = class_basename($e->getModel());
            $resourceName = strtolower($model);

            return ApiResponse::notFound(__('api.resource_not_found', ['resource' => $resourceName]));
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            // Check if this is a route model binding failure (deleted/not found resource)
            $path = $request->path();

            // Extract resource type from URL path
            if (preg_match('#/organizations/[^/]+#', $path)) {
                return ApiResponse::notFound(__('api.resource_not_found', ['resource' => 'organization']));
            } elseif (preg_match('#/projects/[^/]+#', $path)) {
                return ApiResponse::notFound(__('api.resource_not_found', ['resource' => 'project']));
            } elseif (preg_match('#/tasks/[^/]+#', $path)) {
                return ApiResponse::notFound(__('api.resource_not_found', ['resource' => 'task']));
            } elseif (preg_match('#/comments/[^/]+#', $path)) {
                return ApiResponse::notFound(__('api.resource_not_found', ['resource' => 'comment']));
            } elseif (preg_match('#/tags/[^/]+#', $path)) {
                return ApiResponse::notFound(__('api.resource_not_found', ['resource' => 'tag']));
            }

            // Generic endpoint not found
            return ApiResponse::notFound(__('api.route_not_found'));
        });

        $exceptions->render(function (ValidationException $e, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return ApiResponse::validationError('Validation failed.', $e->errors());
        });

        $exceptions->render(function (Throwable $e, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            logger()->error($e->getMessage(), ['exception' => $e]);

            return ApiResponse::error('An unexpected error occurred.');
        });
    })->create();
