<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Test\Functional\JavaScript;

use Facebook\WebDriver\Exception\JavascriptErrorException;
use Zemasterkrom\CloudflareTurnstileBundle\Test\BundleTestingKernel;
use Zemasterkrom\CloudflareTurnstileBundle\Test\Functional\ZmkrCloudflareTurnstileBundleFunctionalTestCase;

/**
 * Functional test class checking the behavior of the Cloudflare Turnstile JavaScript widget handler (required property)
 */
class CloudflareTurnstileTypeRequiredTest extends ZmkrCloudflareTurnstileBundleFunctionalTestCase
{
    public function testCaptchaRequiredModeWithoutFormThrowsException(): void
    {
        $this->expectException(JavascriptErrorException::class);

        $this->client->executeScript("window.zmkrCloudflareTurnstileBundleCaptcha.required();");
    }

    public function testCaptchaRequiredModeWithFormSucceeds(): void
    {
        $this->client->executeScript(<<<EOF
            let form = document.createElement('form');

            document.body.append(form);

            window.zmkrCloudflareTurnstileBundleCaptcha.required(form);
EOF);
        $this->assertNotEmpty($this->getDocumentBodyHtml());
    }

    public function testCaptchaRequiredModeWithFormOnChildNodeSucceeds(): void
    {
        $this->client->executeScript(<<<EOF
            let formWithChildNode = document.createElement('form');
            formWithChildNode.append(document.createElement('div'));

            document.body.append(formWithChildNode);

            window.zmkrCloudflareTurnstileBundleCaptcha.required(formWithChildNode.childNodes[0]);
EOF);
        $this->assertNotEmpty($this->getDocumentBodyHtml());
    }

    public function testCaptchaRequiredModeWithoutCaptchaResponsePreventsSubmission(): void
    {
        $this->client->executeScript(<<<EOF
                let widgetContainerWithoutCaptchaResponse = document.createElement('form');
                widgetContainerWithoutCaptchaResponse.setAttribute('action', 'about:blank');
                widgetContainerWithoutCaptchaResponse.setAttribute('method', 'GET');

                let submitButton = document.createElement('input');
                submitButton.setAttribute('type', 'submit');
                submitButton.setAttribute('value', 'Submit');

                widgetContainerWithoutCaptchaResponse.append(submitButton);
                document.body.append(widgetContainerWithoutCaptchaResponse);

                window.zmkrCloudflareTurnstileBundleCaptcha.required(widgetContainerWithoutCaptchaResponse.childNodes[0], 'cf-turnstile-response');
                submitButton.click();
EOF);
        $this->assertNotEmpty($this->getDocumentBodyHtml());
    }

    public function testCaptchaRequiredModeWithEmptyCaptchaResponsePreventsSubmission(): void
    {
        $this->client->executeScript(<<<EOF
                let widgetContainerWithEmptyCaptchaResponse = document.createElement('form');
                widgetContainerWithEmptyCaptchaResponse.setAttribute('action', 'about:blank');
                widgetContainerWithEmptyCaptchaResponse.setAttribute('method', 'GET');

                let emptyCaptchaResponse = document.createElement('input');
                emptyCaptchaResponse.setAttribute('type', 'hidden');
                emptyCaptchaResponse.setAttribute('name', 'cf-turnstile-response');

                let submitButton = document.createElement('input');
                submitButton.setAttribute('type', 'submit');
                submitButton.setAttribute('value', 'Submit');

                widgetContainerWithEmptyCaptchaResponse.append(emptyCaptchaResponse);
                widgetContainerWithEmptyCaptchaResponse.append(submitButton);
                document.body.append(widgetContainerWithEmptyCaptchaResponse);

                window.zmkrCloudflareTurnstileBundleCaptcha.required(widgetContainerWithEmptyCaptchaResponse.childNodes[0], 'cf-turnstile-response');
                submitButton.click();
EOF);
        $this->assertNotEmpty($this->getDocumentBodyHtml());
    }

    public function testCaptchaRequiredModeWithUnmatchedNameCaptchaResponsePreventsSubmission(): void
    {
        $this->client->executeScript(<<<EOF
                let widgetContainerWithCaptchaResponse = document.createElement('form');
                widgetContainerWithCaptchaResponse.setAttribute('action', 'about:blank');
                widgetContainerWithCaptchaResponse.setAttribute('method', 'GET');

                let captchaResponse = document.createElement('input');
                captchaResponse.setAttribute('type', 'hidden');
                captchaResponse.setAttribute('name', 'cf-turnstile-response');
                captchaResponse.setAttribute('value', 'XXX-DUMMY-TOKEN-XXX');

                let submitButton = document.createElement('input');
                submitButton.setAttribute('type', 'submit');
                submitButton.setAttribute('value', 'Submit');

                widgetContainerWithCaptchaResponse.append(captchaResponse);
                widgetContainerWithCaptchaResponse.append(submitButton);
                document.body.append(widgetContainerWithCaptchaResponse);

                window.zmkrCloudflareTurnstileBundleCaptcha.required(widgetContainerWithCaptchaResponse.childNodes[0], 'cf-turnstile-test-response');
                submitButton.click();
EOF);
        $this->assertNotEmpty($this->getDocumentBodyHtml());
    }

    public function testCaptchaRequiredModeWithCaptchaResponseApprovesSubmission(): void
    {
        $this->client->executeScript(<<<EOF
                let widgetContainerWithCaptchaResponse = document.createElement('form');
                widgetContainerWithCaptchaResponse.setAttribute('action', 'about:blank');
                widgetContainerWithCaptchaResponse.setAttribute('method', 'GET');

                let captchaResponse = document.createElement('input');
                captchaResponse.setAttribute('type', 'hidden');
                captchaResponse.setAttribute('name', 'cf-turnstile-response');
                captchaResponse.setAttribute('value', 'XXX-DUMMY-TOKEN-XXX');

                let submitButton = document.createElement('input');
                submitButton.setAttribute('type', 'submit');
                submitButton.setAttribute('value', 'Submit');

                widgetContainerWithCaptchaResponse.append(captchaResponse);
                widgetContainerWithCaptchaResponse.append(submitButton);
                document.body.append(widgetContainerWithCaptchaResponse);

                window.zmkrCloudflareTurnstileBundleCaptcha.required(widgetContainerWithCaptchaResponse.childNodes[0], 'cf-turnstile-response');
                submitButton.click();
EOF);

        // Wait for body refresh after submit
        $timeout = 10;
        $interval = 500;
        $start = microtime(true);

        while (microtime(true) - $start < $timeout) {
            $body = $this->getDocumentBodyHtml();

            if (strpos($body, $this->getShortClassName()) === false) {
                $this->assertEmpty($body);
                return;
            }

            usleep($interval * 1000);
        }

        $this->fail('Failed to wait for document body refresh after form submission');
    }

    protected static function getKernelClass(): string
    {
        return BundleTestingKernel::class;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        static::$class = null;
    }
}
