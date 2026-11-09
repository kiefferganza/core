<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use JsonSerializable;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

abstract class HttpException extends RuntimeException implements HttpExceptionInterface, JsonSerializable
{
    /**
     * The friendly code for this exception.
     */
    protected string $errorCode = 'RUNTIME_ERROR';

    /**
     * Default http status code when the exception is rendered.
     */
    protected int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;

    /**
     * Default http headers to be used when the exception is rendered.
     */
    protected array $headers = [];

    public static function make(): static
    {
        return new static();
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function withMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function withStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function withHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'error_code' => $this->getErrorCode(),
            'message' => $this->getMessage(),
            'http_status' => $this->getStatusCode(),
        ];
    }

    public function report(): bool
    {
        return false;
    }

    public function render(Request $request): JsonResponse
    {
        return response()
            ->json(
                [
                    'message' => $this->getMessage(),
                    'error_code' => $this->getErrorCode(),
                    'http_status' => $this->getStatusCode(),
                    'success' => false,
                ],
                $this->getStatusCode(),
                $this->getHeaders()
            );
    }
}
