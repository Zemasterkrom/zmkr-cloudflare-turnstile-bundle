<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Contains the validation properties of the Cloudflare Turnstile captcha constraint definition
 */
class CloudflareTurnstileCaptcha extends Constraint
{
    const DEFAULT_RESPONSE_FIELD_NAME = 'cf-turnstile-response';

    public string $message = 'zmkr_cloudflare_turnstile.rejected_captcha';
    public string $responseFieldName = self::DEFAULT_RESPONSE_FIELD_NAME;

    /**
     * CloudflareTurnstileCaptcha captcha constraint properties initializer
     *
     * @param string|null $message Error message used when validation fails
     * @param string|null $responseFieldName Cloudflare Turnstile captcha hidden response field name to use for validation
     * @param mixed $options The options (as associative array) or the value for the default option (any other type)
     * @param string[] $groups An array of validation groups
     * @param mixed $payload Domain-specific data attached to a constraint
     */
    public function __construct(string $message = null, string $responseFieldName = null, $options = null, array $groups = null, $payload = null)
    {
        parent::__construct($options, $groups, $payload);

        $this->message = $message ?? $this->message;
        $this->responseFieldName = $responseFieldName ?? $this->responseFieldName;
    }
}
