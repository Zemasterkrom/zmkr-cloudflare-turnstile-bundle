<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Client;

use Zemasterkrom\CloudflareTurnstileBundle\Exception\CloudflareTurnstileApiException;

/**
 * Cloudflare Turnstile Bundle client interface that integrates methods to verify the provided captcha response
 */
interface CloudflareTurnstileClientInterface
{
    /**
     * Combines and filters default and custom options for HTTP requests
     *
     * @param array<string, mixed> ...$options Additional HTTP options array
     *
     * @return array<string, mixed> Filtered array of combined options
     */
    public function handleOptions(array ...$options): array;

    /**
     * Verify the provided Cloudflare Turnstile captcha response
     *
     * @param mixed $captchaResponse Cloudflare Turnstile captcha response
     * @param array<string, mixed> $options Array of additional options
     *
     * @return bool True if the verification is successful, false otherwise
     *
     * @throws CloudflareTurnstileApiException If HTTP verification fails due to an API or HTTP-related issue
     */
    public function verify($captchaResponse, array $options = []): bool;
}
