<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Validator;

use Symfony\Component\Validator\Constraint;

class CloudflareTurnstileCaptcha extends Constraint
{
    const DEFAULT_RESPONSE_FIELD_NAME = 'cf-turnstile-response';

    public string $message = 'zmkr_cloudflare_turnstile.rejected_captcha';
    public string $responseFieldName = self::DEFAULT_RESPONSE_FIELD_NAME;

    public function __construct(?string $message = null, ?string $responseFieldName = null, $options = null, ?array $groups = null, $payload = null)
    {
        parent::__construct($options, $groups, $payload);

        $this->message = $message ?? $this->message;
        $this->responseFieldName = $responseFieldName ?? $this->responseFieldName;
    }
}
