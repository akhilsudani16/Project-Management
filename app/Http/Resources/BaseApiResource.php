<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\ApiResponseStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseApiResource extends JsonResource
{
    protected ApiResponseStatus $status = ApiResponseStatus::SUCCESS;

    protected ?string $message = null;

    protected ?array $errors = null;

    protected ?array $meta = null;

    protected int $statusCode = Response::HTTP_OK;

    public function withStatus(ApiResponseStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function withMessage(?string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function withErrors(?array $errors): static
    {
        $this->errors = $errors;

        return $this;
    }

    public function withMeta(?array $meta): static
    {
        $this->meta = $meta;

        return $this;
    }

    public function withStatusCode(int $statusCode): static
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function toArray(Request $request): array
    {
        return [
            'status' => $this->status->value,
            'message' => $this->message,
            'data' => $this->transformData($request),
            'errors' => $this->errors ? (object) $this->errors : null,
            'meta' => $this->meta ? (object) $this->meta : null,
        ];
    }

    public function toResponse($request): JsonResponse
    {
        return parent::toResponse($request)->setStatusCode($this->statusCode);
    }

    abstract protected function transformData(Request $request): ?object;
}
