<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Exception;

/**
 * CloudflareTurnstileException base class is used for exceptions that may occur in Cloudflare
 * Turnstile verification process. It provides the ability to separate Symfony exceptions from
 * Cloudflare Turnstile exceptions and categorize them.
 */
class CloudflareTurnstileException extends \Exception
{
    const NON_CRITICAL_ERROR = 0;
    const CRITICAL_ERROR = 1;

    /**
     * Constructor for the CloudflareTurnstileException
     *
     * @param string $message The error message describing the exception
     * @param \Throwable $previous The previous exception used for chaining (default is null)
     * @param int $code The error code, which can be set as non-critical or critical (default is non-critical)
     */
    public function __construct(string $message, \Throwable $previous = null, int $code = self::NON_CRITICAL_ERROR)
    {
        parent::__construct($message, $code, $previous);
    }
}
