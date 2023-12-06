<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Exception;

use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;

/**
 * Cloudflare Turnstile exception related to API issue
 */
class CloudflareTurnstileApiException extends CloudflareTurnstileException
{
}
