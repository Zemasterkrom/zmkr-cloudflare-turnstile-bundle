<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Exception;

use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;

/**
 * Cloudflare Turnstile exception related to incoming request / data issue
 */
class CloudflareTurnstileInvalidResponseException extends CloudflareTurnstileException
{
    public function __construct(string $message, RequestExceptionInterface $previous = null, int $code = self::NON_CRITICAL_ERROR)
    {
        parent::__construct($message, $previous, $code);
    }
}
