<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Zemasterkrom\CloudflareTurnstileBundle\Client\CloudflareTurnstileClientInterface;
use Zemasterkrom\CloudflareTurnstileBundle\Manager\CloudflareTurnstileErrorManager;
use Zemasterkrom\CloudflareTurnstileBundle\Exception\CloudflareTurnstileException;

/**
 * This class represents the core component of the bundle.
 * It allows to validate or reject the response provided by the Cloudflare Turnstile captcha.
 */
class CloudflareTurnstileCaptchaValidator extends ConstraintValidator
{
    private CloudflareTurnstileClientInterface $client;
    private CloudflareTurnstileErrorManager $errorManager;
    private bool $enabled;

    /**
     * Constructor for the validator.
     *
     * @param CloudflareTurnstileClientInterface $client The client for making Cloudflare Turnstile API requests
     * @param CloudflareTurnstileErrorManager $errorManager The error manager which handles or ignore Cloudflare Turnstile HTTP errors
     * @param bool $enabled Flag indicating whether the captcha is enabled
     */
    public function __construct(CloudflareTurnstileClientInterface $client, CloudflareTurnstileErrorManager $errorManager, bool $enabled)
    {
        $this->client = $client;
        $this->errorManager = $errorManager;
        $this->enabled = $enabled;
    }

    /**
     * Validates the Cloudflare Turnstile captcha response
     *
     * @param mixed $value Provided Cloudflare Turnstile captcha response token
     * @param Constraint $constraint The captcha constraint being validated
     *
     * @throws UnexpectedTypeException If provided constraint is not a Cloudflare Turnstile constraint
     * @throws CloudflareTurnstileException If there's an issue with the Cloudflare Turnstile verification API call (if error manager handling is enabled)
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof CloudflareTurnstileCaptcha) {
            throw new UnexpectedTypeException($constraint, CloudflareTurnstileCaptcha::class);
        }

        if (!$this->enabled) {
            return;
        }

        try {
            if (!$this->client->verify($value)) {
                $this->context->buildViolation($constraint->message)->addViolation();
            }
        } catch (CloudflareTurnstileException $e) {
            $this->context->buildViolation($constraint->message)->addViolation();
            $this->errorManager->throwIfExplicitErrorsEnabled($e);
        }
    }
}
