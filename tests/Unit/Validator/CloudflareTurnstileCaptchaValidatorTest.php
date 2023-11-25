<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Tests\Validator;

use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Zemasterkrom\CloudflareTurnstileBundle\Client\CloudflareTurnstileClient;
use Zemasterkrom\CloudflareTurnstileBundle\Client\CloudflareTurnstileClientInterface;
use Zemasterkrom\CloudflareTurnstileBundle\ErrorManager\CloudflareTurnstileErrorManager;
use Zemasterkrom\CloudflareTurnstileBundle\Exception\CloudflareTurnstileApiException;
use Zemasterkrom\CloudflareTurnstileBundle\Exception\CloudflareTurnstileInvalidResponseException;
use Zemasterkrom\CloudflareTurnstileBundle\Validator\CloudflareTurnstileCaptcha;
use Zemasterkrom\CloudflareTurnstileBundle\Validator\CloudflareTurnstileCaptchaValidator;

/**
 * Clouflare turnstile validator test class.
 * Checks that form violation errors are raised, that exception handling is correct and that client verification is correctly connected.
 */
class CloudflareTurnstileCaptchaValidatorTest extends ConstraintValidatorTestCase
{
    private CloudflareTurnstileErrorManager $errorManager;
    private RequestStack|MockObject $mockedRequestStack;
    private $mockedCaptchaResponse;

    /**
     * Initializes default properties for the test and the validator :
     * - Disabled error manager ;
     * - Default request
     * - Empty JSON message.
     */
    public function setUp(): void
    {
        $this->errorManager = new CloudflareTurnstileErrorManager(false);
        $this->mockedRequestStack = new RequestStack();
        $this->mockedRequestStack->push(new Request());
        $this->mockedCaptchaResponse = json_encode([]);

        parent::setUp();
    }

    /**
     * Creates the validator from the properties of the current test.
     *
     * @return CloudflareTurnstileCaptchaValidator Cloudflare Turnstile Captcha Validator with current test properties
     */
    protected function createValidator(): CloudflareTurnstileCaptchaValidator
    {
        $this->validator = new CloudflareTurnstileCaptchaValidator($this->mockedRequestStack, $this->errorManager, new CloudflareTurnstileClient(new MockHttpClient(new MockResponse($this->mockedCaptchaResponse)), '', []));
        $this->context = $this->createContext();
        $this->validator->initialize($this->context);

        return $this->validator;
    }

    /**
     * Creates the validator from the properties of the current test with a custom client implementation.
     * Used for abstracting and hiding implementation details of requests.
     *
     * @param CloudflareTurnstileClientInterface $client Custom client interfaced object
     *
     * @return CloudflareTurnstileCaptchaValidator Cloudflare Turnstile Captcha Validator with current test properties
     */
    protected function createValidatorWithCustomClient(CloudflareTurnstileClientInterface $client): CloudflareTurnstileCaptchaValidator
    {
        $this->validator = new CloudflareTurnstileCaptchaValidator($this->mockedRequestStack, $this->errorManager, $client);
        $this->context = $this->createContext();
        $this->validator->initialize($this->context);

        return $this->validator;
    }

    public function testSuccessfulCaptchaValidation(): void
    {
        $this->mockedCaptchaResponse = json_encode([
            'success' => true
        ]);

        $this->validator = $this->createValidator();
        $this->validator->validate('', new CloudflareTurnstileCaptcha());

        $this->assertNoViolation();
    }

    /**
     * @dataProvider unsuccessfulCaptchaResponses
     */
    public function testUnsuccessfulCaptchaValidation(array $unsuccessfulCaptchaResponse): void
    {
        $this->mockedCaptchaResponse = json_encode($unsuccessfulCaptchaResponse);

        $this->validator = $this->createValidator();
        $this->validator->validate('', new CloudflareTurnstileCaptcha());

        $this->buildViolation('rejected_captcha')->assertRaised();
    }

    public function testDirectUnsuccessfulCaptchaValidationWithEmptyResponse(): void
    {
        $this->mockedRequestStack->push(new Request([], [
            'cf-turnstile-response' => ''
        ]));

        $this->validator = $this->createValidator();
        $this->validator->validate('', new CloudflareTurnstileCaptcha());

        $this->buildViolation('rejected_captcha')->assertRaised();
    }

    public function testInvalidCaptchaConstraintThrowsException(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate('', $this->createMock(Constraint::class));
    }

    public function testInvalidCaptchaClientValidationWithoutExceptionThrowing(): void
    {
        $this->errorManager = new CloudflareTurnstileErrorManager(false);

        $mockedClient = $this->createMock(CloudflareTurnstileClientInterface::class);
        $mockedClient->method('verify')
            ->willThrowException(new CloudflareTurnstileApiException(__METHOD__));

        $this->validator = $this->createValidatorWithCustomClient($mockedClient);
        $this->validator->validate('', new CloudflareTurnstileCaptcha());

        $this->buildViolation('rejected_captcha')->assertRaised();
    }

    public function testInvalidCaptchaClientValidationWithExceptionThrowing(): void
    {
        $this->expectException(CloudflareTurnstileApiException::class);

        $this->errorManager = new CloudflareTurnstileErrorManager(true);

        $mockedClient = $this->createMock(CloudflareTurnstileClientInterface::class);
        $mockedClient->method('verify')
            ->willThrowException(new CloudflareTurnstileApiException(__METHOD__));

        $this->validator = $this->createValidatorWithCustomClient($mockedClient);
        $this->validator->validate('', new CloudflareTurnstileCaptcha());

        $this->buildViolation('rejected_captcha')->assertRaised();
    }

    public function testInvalidCaptchaResponseWithoutExceptionThrowing(): void
    {
        $this->errorManager = new CloudflareTurnstileErrorManager(false);

        // Mocked ParameterBag to keep consistent get behavior between Symfony 5 and 6
        $mockedParameterBag = $this->createMock(ParameterBag::class);
        $mockedParameterBag->method('get')
            ->willReturn([]);

        $mockedRequest = $this->createMock(Request::class);
        $mockedRequest->request = $mockedParameterBag;

        // The simulated RequestStack will return the simulated Request with consistent behavior, which contains non-scalar data and should raise an error during validation
        $this->mockedRequestStack = $this->createMock(RequestStack::class);
        $this->mockedRequestStack->method('getCurrentRequest')
            ->willReturn($mockedRequest);

        $this->validator = $this->createValidator();
        $this->validator->validate('', new CloudflareTurnstileCaptcha());

        // Even if Symfony 5 behavior is currently retained, the received captcha is still invalid!
        $this->buildViolation('rejected_captcha')->assertRaised();
    }

    public function testInvalidCaptchaResponseWithExceptionThrowing(): void
    {
        $this->expectException(CloudflareTurnstileInvalidResponseException::class);

        $this->errorManager = new CloudflareTurnstileErrorManager(true);

        // Mocked ParameterBag to keep consistent get behavior between Symfony 5 and 6
        $mockedParameterBag = $this->createMock(ParameterBag::class);
        $mockedParameterBag->method('get')
            ->willReturn([]);

        $mockedRequest = $this->createMock(Request::class);
        $mockedRequest->request = $mockedParameterBag;

        // The simulated RequestStack will return the simulated Request with consistent behavior, which contains non-scalar data and should raise an error during validation
        $this->mockedRequestStack = $this->createMock(RequestStack::class);
        $this->mockedRequestStack->method('getCurrentRequest')
            ->willReturn($mockedRequest);

        $this->validator = $this->createValidator();
        $this->validator->validate('', new CloudflareTurnstileCaptcha());

        // Even if Symfony 5 behavior is currently retained, the received captcha is still invalid!
        $this->buildViolation('rejected_captcha')->assertRaised();
    }

    public function unsuccessfulCaptchaResponses(): iterable
    {
        yield [[
            'success' => false
        ]];

        yield [[
            'test' => false
        ]];

        yield [[]];
    }
}
