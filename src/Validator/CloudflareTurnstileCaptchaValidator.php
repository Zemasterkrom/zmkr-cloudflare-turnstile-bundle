<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Validator;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Zemasterkrom\CloudflareTurnstileBundle\ErrorManager\CloudflareTurnstileErrorManager;
use Zemasterkrom\CloudflareTurnstileBundle\Exception\CloudflareTurnstileHttpException;

/**
 * This class represents the core component of the bundle.
 * It allows to validate or reject the response provided by the Cloudflare Turnstile captcha.
 */
class CloudflareTurnstileCaptchaValidator extends ConstraintValidator
{
    private RequestStack $requestStack;

    private HttpClientInterface $httpClient;

    private CloudflareTurnstileErrorManager $errorManager;

    private string $secretKey;

    /**
     * Constructor for the validator.
     *
     * @param RequestStack $requestStack The RequestStack for accessing the current request.
     * @param HttpClientInterface $httpClient The HTTP client for making API requests.
     * @param CloudflareTurnstileErrorManager $cloudflareTurnstileErrorManager The error manager which handles or ignore Cloudflare Turnstile HTTP errors.
     * @param string $secretKey The Cloudflare Turnstile secret key.
     */
    public function __construct(RequestStack $requestStack, HttpClientInterface $httpClient, CloudflareTurnstileErrorManager $cloudflareTurnstileErrorManager, string $secretKey)
    {
        $this->requestStack = $requestStack;
        $this->httpClient = $httpClient;
        $this->errorManager = $cloudflareTurnstileErrorManager;
        $this->secretKey = $secretKey;
    }

    /**
     * Validates the Cloudflare Turnstile captcha response.
     *
     * @param mixed $value The input text value that is not used due to the captcha response type (hidden response).
     * @param Constraint $constraint The captcha constraint being validated.
     *
     * @throws CloudflareTurnstileHttpException If there's an issue with the Cloudflare Turnstile verification API call.
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        $captchaResponse = $this->requestStack->getCurrentRequest()->request->get('cf-turnstile-response'); // Provided by the hidden input field with name cf-turnstile-response

        if ($captchaResponse === '') {
            $this->context->buildViolation($constraint->message)->addViolation();
            return;
        }

        try {
            $apiResponse = $this->httpClient->request(
                'POST',
                'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                [
                    'body' => [
                        'response' => $captchaResponse,
                        'secret' => $this->secretKey
                    ]
                ]
            )->toArray();

            if (!isset($apiResponse['success']) || !$apiResponse['success']) {
                $this->context->buildViolation($constraint->message)->addViolation();
            }
        } catch (HttpClientExceptionInterface $e) {
            $this->context->buildViolation($constraint->message)->addViolation();
            $this->errorManager->throwIfExplicitErrorsEnabled(new CloudflareTurnstileHttpException('Unable to call Cloudflare Turnstile API with provided parameters', $e));
        }
    }
}
