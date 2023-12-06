<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Exception;

use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;

/**
 * Cloudflare Turnstile exception related to incoming request / data issue
 */
class CloudflareTurnstileInvalidResponseException extends CloudflareTurnstileException
{
}
