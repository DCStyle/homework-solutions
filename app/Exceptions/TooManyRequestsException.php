<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TooManyRequestsException extends HttpException
{
    /**
     * Create a new rate limit exceeded exception instance.
     *
     * @param string|null     $message  The internal exception message
     * @param \Throwable|null $previous The previous exception
     * @param array          $headers   Additional response headers
     * @param int            $code      The internal exception code
     */
    public function __construct(
        string $message = null,
        \Throwable $previous = null,
        array $headers = [],
        int $code = 0
    ) {
        $message = $message ?? 'Too many requests. Please try again later.';

        // Merge rate limit headers with any additional headers
        $defaultHeaders = [
            'Retry-After' => 60, // Suggest client to wait for 60 seconds
            'X-RateLimit-Reset' => time() + 60,
        ];

        $headers = array_merge($defaultHeaders, $headers);

        parent::__construct(
            Response::HTTP_TOO_MANY_REQUESTS, // 429 status code
            $message,
            $previous,
            $headers,
            $code
        );
    }
}
