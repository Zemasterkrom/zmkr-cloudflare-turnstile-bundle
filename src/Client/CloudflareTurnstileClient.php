<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Client;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;
use Zemasterkrom\CloudflareTurnstileBundle\Exception\CloudflareTurnstileApiException;

/**
 * Cloudflare Turnstile client verifier using the siteverify POST API
 */
class CloudflareTurnstileClient implements CloudflareTurnstileClientInterface
{
    private HttpClientInterface $httpClient;
    private string $secretKey;
    private array $options;

    /**
     * Constructor for CloudflareTurnstileClient
     *
     * @param HttpClientInterface $httpClient The HTTP client for making API requests
     * @param string $secretKey The registered Cloudflare Turnstile secret key
     * @param array $options Additional options for HTTP requests. Allowed options : body, timeout, max_duration.
     */
    public function __construct(HttpClientInterface $httpClient, string $secretKey, array $options)
    {
        $this->httpClient = $httpClient;
        $this->secretKey = $secretKey;
        $this->options = $this->handleOptions($options);
    }

    public function handleOptions(array ...$options): array
    {
        return array_filter(array_merge(isset($this->options) ? $this->options : [], ...$options), function ($optionName) {
            return $optionName === 'body' || $optionName === 'timeout' || $optionName === 'max_duration';
        }, ARRAY_FILTER_USE_KEY);
    }

    public function verify($captchaResponse, array $options = []): bool
    {
        try {
            $apiResponse = $this->httpClient->request(
                'POST',
                'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                $this->handleOptions(
                    $options,
                    [
                        'body' => [
                            'response' => $captchaResponse,
                            'secret' => $this->secretKey
                        ]
                    ]
                )
            )->toArray();

            return isset($apiResponse['success']) && $apiResponse['success'];
        } catch (HttpClientExceptionInterface $e) {
            throw new CloudflareTurnstileApiException('Unable to call Cloudflare Turnstile API with provided parameters', $e);
        }
    }
}
