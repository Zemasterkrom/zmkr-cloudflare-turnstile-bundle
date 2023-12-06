<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Exception;

use UnexpectedValueException;

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
     * @param object|null $previous The previous exception used for chaining (default is null)
     * @param int $code The error code, which can be set as non-critical or critical (default is non-critical)
     */
    public function __construct(string $message, object $previous = null, int $code = self::NON_CRITICAL_ERROR)
    {
        // Since the exception interfaces provided by Symfony do not necessarily extend the Throwable interface, we need to check the exception type dynamically
        if (null !== $previous && !$previous instanceof \Throwable) {
            throw new UnexpectedValueException(get_class($previous) . ' is not a Throwable instance');
        }

        parent::__construct($message, $code, $previous);
    }
}
