<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\ApiResponseStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseApiCollection extends ResourceCollection
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

    public function toResponse($request): JsonResponse
    {
        return parent::toResponse($request)->setStatusCode($this->statusCode);
    }

    protected function buildMeta(): ?object
    {
        $meta = $this->meta ?? [];

        if ($this->resource instanceof LengthAwarePaginator) {
            $meta['pagination'] = [
                'total' => $this->resource->total(),
                'count' => $this->resource->count(),
                'per_page' => $this->resource->perPage(),
                'current_page' => $this->resource->currentPage(),
                'total_pages' => $this->resource->lastPage(),
            ];
        }

        return ! empty($meta) ? (object) $meta : null;
    }
}
