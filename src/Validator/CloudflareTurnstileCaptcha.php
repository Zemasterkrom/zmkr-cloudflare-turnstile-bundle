<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Validator;

use Symfony\Component\Validator\Constraint;

class CloudflareTurnstileCaptcha extends Constraint
{
    public string $message = 'rejected_captcha';
}
