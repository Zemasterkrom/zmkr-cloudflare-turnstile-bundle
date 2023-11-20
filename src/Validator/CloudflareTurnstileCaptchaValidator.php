<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Validator;

use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Zemasterkrom\CloudflareTurnstileBundle\Client\CloudflareTurnstileClientInterface;
use Zemasterkrom\CloudflareTurnstileBundle\ErrorManager\CloudflareTurnstileErrorManager;
use Zemasterkrom\CloudflareTurnstileBundle\Exception\CloudflareTurnstileApiException;
use Zemasterkrom\CloudflareTurnstileBundle\Exception\CloudflareTurnstileInvalidResponseException;

/**
 * This class represents the core component of the bundle.
 * It allows to validate or reject the response provided by the Cloudflare Turnstile captcha.
 */
class CloudflareTurnstileCaptchaValidator extends ConstraintValidator
{
    private RequestStack $requestStack;

    private CloudflareTurnstileErrorManager $errorManager;

    private CloudflareTurnstileClientInterface $client;

    /**
     * Constructor for the validator.
     *
     * @param RequestStack $requestStack The RequestStack for accessing the current request.
     * @param HttpClientInterface $httpClient The HTTP client for making API requests.
     * @param CloudflareTurnstileErrorManager $cloudflareTurnstileErrorManager The error manager which handles or ignore Cloudflare Turnstile HTTP errors.
     * @param string $secretKey The Cloudflare Turnstile secret key.
     */
    public function __construct(RequestStack $requestStack, CloudflareTurnstileErrorManager $cloudflareTurnstileErrorManager, CloudflareTurnstileClientInterface $cloudflareTurnstileClient)
    {
        $this->requestStack = $requestStack;
        $this->errorManager = $cloudflareTurnstileErrorManager;
        $this->client = $cloudflareTurnstileClient;
    }

    /**
     * Validates the Cloudflare Turnstile captcha response
     *
     * @param mixed $value The input text value that is not used due to the captcha response type (hidden response)
     * @param Constraint $constraint The captcha constraint being validated
     *
     * @throws CloudflareTurnstileInvalidResponseException If the response returned by Cloudflare Turnstile has been modified / is invalid (if error manager handling is enabled)
     * @throws CloudflareTurnstileApiException If there's an issue with the Cloudflare Turnstile verification API call (if error manager handling is enabled)
     */
    public function validate($value, Constraint $constraint): void
    {
        try {
            $captchaResponse = $this->requestStack->getCurrentRequest()->request->get('cf-turnstile-response'); // Provided by the hidden input field with name cf-turnstile-response
        } catch (RequestExceptionInterface $e) {
            $this->context->buildViolation($constraint->message)->addViolation();
            $this->errorManager->throwIfExplicitErrorsEnabled(new CloudflareTurnstileInvalidResponseException('Invalid Cloudflare Turnstile response', $e));
            return;
        }

        if ($captchaResponse === '') {
            $this->context->buildViolation($constraint->message)->addViolation();
            return;
        }

        try {
            if (!$this->client->verify($captchaResponse)) {
                $this->context->buildViolation($constraint->message)->addViolation();
            }
        } catch (CloudflareTurnstileApiException $e) {
            $this->context->buildViolation($constraint->message)->addViolation();
            $this->errorManager->throwIfExplicitErrorsEnabled($e);
        }
    }
}
