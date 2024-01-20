<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Test\Unit\Validator;

use DG\BypassFinals;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Zemasterkrom\CloudflareTurnstileBundle\Client\CloudflareTurnstileClientInterface;
use Zemasterkrom\CloudflareTurnstileBundle\Exception\CloudflareTurnstileApiException;
use Zemasterkrom\CloudflareTurnstileBundle\Validator\CloudflareTurnstileCaptcha;
use Zemasterkrom\CloudflareTurnstileBundle\Validator\CloudflareTurnstileCaptchaValidator;
use Zemasterkrom\CloudflareTurnstileBundle\Manager\CloudflareTurnstileErrorManager;

/**
 * Clouflare turnstile validator test class.
 * Checks that form violation errors are raised, that exception handling is correct and that client verification is correctly connected.
 */
class CloudflareTurnstileCaptchaValidatorTest extends ConstraintValidatorTestCase
{
    private CloudflareTurnstileErrorManager $errorManager;
    private CloudflareTurnstileClientInterface $client;
    private bool $enabled;

    /**
     * Initializes default properties for the test and the validator :
     * - Disabled error manager ;
     * - Default request ;
     * - Empty JSON message.
     */
    public function setUp(): void
    {
        $this->errorManager = new CloudflareTurnstileErrorManager(true);
        $this->client = $this->createMock(CloudflareTurnstileClientInterface::class);
        $this->enabled = true;

        parent::setUp();
    }

    /**
     * Creates the validator from the properties of the current test
     *
     * @return CloudflareTurnstileCaptchaValidator Cloudflare Turnstile Captcha Validator with current test properties
     */
    protected function createValidator(): CloudflareTurnstileCaptchaValidator
    {
        $this->validator = new CloudflareTurnstileCaptchaValidator($this->client, $this->errorManager, $this->enabled);
        $this->context = $this->createContext();
        $this->validator->initialize($this->context);

        return $this->validator;
    }

    public function testInvalidCaptchaConstraintThrowsException(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate('', $this->createMock(Constraint::class));
    }

    public function testSuccessfulCaptchaValidation(): void
    {
        $client = $this->createMock(CloudflareTurnstileClientInterface::class);
        $client->method('verify')->willReturn(true);
        $this->client = $client;

        $cloudflareTurnstileCaptchaConstraint = new CloudflareTurnstileCaptcha();

        $this->validator = $this->createValidator();
        $this->validator->validate('', $cloudflareTurnstileCaptchaConstraint);

        $this->assertNoViolation();
    }

    public function testUnsuccessfulCaptchaValidation(): void
    {
        $client = $this->createMock(CloudflareTurnstileClientInterface::class);
        $client->method('verify')->willReturn(false);
        $this->client = $client;

        $cloudflareTurnstileCaptchaConstraint = new CloudflareTurnstileCaptcha();

        $this->validator = $this->createValidator();
        $this->validator->validate('', $cloudflareTurnstileCaptchaConstraint);

        $this->buildViolation($cloudflareTurnstileCaptchaConstraint->message)->assertRaised();
    }

    public function testUnsuccessfulCaptchaValidationWithCustomInvalidMessageOption(): void
    {
        $client = $this->createMock(CloudflareTurnstileClientInterface::class);
        $client->method('verify')->willReturn(false);
        $this->client = $client;

        $cloudflareTurnstileCaptchaConstraint = new CloudflareTurnstileCaptcha();
        $cloudflareTurnstileCaptchaConstraint->message = 'custom_rejected_captcha';

        $this->validator = $this->createValidator();
        $this->validator->validate('', $cloudflareTurnstileCaptchaConstraint);

        $this->buildViolation('custom_rejected_captcha')->assertRaised();
    }

    public function testCaptchaValidationIsIgnoredWhenDisabled(): void
    {
        $client = $this->createMock(CloudflareTurnstileClientInterface::class);
        $client->method('verify')->willThrowException(new CloudflareTurnstileApiException(__METHOD__));
        $this->client = $client;
        $this->enabled = false;

        $this->validator = $this->createValidator();
        $this->validator->validate('', new CloudflareTurnstileCaptcha());

        $this->assertNoViolation();
    }

    public function testInvalidCaptchaClientValidationThrowsException(): void
    {
        $this->expectException(CloudflareTurnstileApiException::class);

        $this->errorManager = new CloudflareTurnstileErrorManager(true);

        $client = $this->createMock(CloudflareTurnstileClientInterface::class);
        $client->method('verify')->willThrowException(new CloudflareTurnstileApiException(__METHOD__));
        $this->client = $client;

        $cloudflareTurnstileCaptchaConstraint = new CloudflareTurnstileCaptcha();

        $this->validator = $this->createValidator();
        $this->validator->validate('', $cloudflareTurnstileCaptchaConstraint);

        // Even if Symfony 5 behavior is currently retained, the received captcha is still invalid!
        $this->buildViolation($cloudflareTurnstileCaptchaConstraint->message)->assertRaised();
    }
}
