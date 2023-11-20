<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Exception;

use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;

/**
 * Cloudflare Turnstile exception related to API issue
 */
class CloudflareTurnstileApiException extends CloudflareTurnstileException
{
    public function __construct(string $message, HttpClientExceptionInterface $previous = null, int $code = self::NON_CRITICAL_ERROR)
    {
        parent::__construct($message, $previous, $code);
    }
}
