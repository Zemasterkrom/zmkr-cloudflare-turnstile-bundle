<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\ErrorManager;

use Zemasterkrom\CloudflareTurnstileBundle\Exception\CloudflareTurnstileException;

/**
 * Error manager that handles Cloudflare Turnstile bundle exceptions based on provided settings
 */
class CloudflareTurnstileErrorManager
{
    private bool $throwOnCoreFailure;

    /**
     * Constructor of the error manager
     *
     * @param bool $throwOnCoreFailure Flag to enable or disable the error manager
     */
    public function __construct(bool $throwOnCoreFailure)
    {
        $this->throwOnCoreFailure = $throwOnCoreFailure;
    }

    /**
     * Throws a managed bundle exception if allowed
     *
     * @param CloudflareTurnstileException $cloudflareTurnstileException Cloudflare Turnstile bundle exception
     */
    public function throwIfExplicitErrorsEnabled(CloudflareTurnstileException $cloudflareTurnstileException)
    {
        if ($this->throwOnCoreFailure) {
            throw $cloudflareTurnstileException;
        }
    }
}
