<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Test\Functional\Form\Type;

use Facebook\WebDriver\Exception\JavascriptErrorException;
use ReflectionClass;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;
use Zemasterkrom\CloudflareTurnstileBundle\Test\ValidBundleTestingKernel;

/**
 * Functional test class checking the behavior of the Cloudflare Turnstile JavaScript widget handler (required property)
 */
class CloudflareTurnstileTypeRequiredFunctionalTest extends PantherTestCase
{
    private Client $client;
    private array $registeredPorts;

    public function setUp(): void
    {
        $javascriptWidgetHandlerVersionedFileName = glob(__DIR__ . '/../../../../src/Resources/public/js/zmkr_cloudflare_turnstile_widget_handler*.js');

        if (!$javascriptWidgetHandlerVersionedFileName || count($javascriptWidgetHandlerVersionedFileName) > 1) {
            throw new \RuntimeException('Unable to find unique versioned JavaScript widget handler. Make sure only one associated file is present.');
        }

        $javascriptWidgetHandler = file_get_contents($javascriptWidgetHandlerVersionedFileName[0]);

        if (!$javascriptWidgetHandler) {
            throw new \RuntimeException('Unable to load the JavaScript widget handler');
        }

        /** @var string */
        $widgetHandlerJavascriptFunctions = preg_replace("/^[^\s].+[(].*[)];*$/m", '', $javascriptWidgetHandler);

        /** @var string */
        $widgetHandlerJavascriptFunctions = preg_replace("/^\s*function\s+(\w+)\s*\(([^)]*)\)\s*{/m", "window.$1 = function ($2) {", $widgetHandlerJavascriptFunctions);

        $this->client = static::createPantherClient([
            'browser' => static::FIREFOX,
            'port' => $this->getAvailablePort()
        ], [], [
            'port' => $this->getAvailablePort()
        ]);
        $this->client->start();
        $this->client->executeScript($widgetHandlerJavascriptFunctions);
        $this->client->executeScript(<<<EOF
            let title = document.createElement('h1');
            title.textContent = "{$this->getShortClassName()}";

            document.body.innerHTML = '';
            document.body.append(title);
EOF);
    }

    private function getAvailablePort(int $count = 1, int $retryLimit = 2): int
    {
        if ($count >= $retryLimit) {
            throw new \RuntimeException("Failed to acquire an available port within $retryLimit attempts");
        }

        $socket = socket_create_listen(0);

        if ($socket) {
            socket_getsockname($socket, $address, $port);
            socket_close($socket);

            if (!in_array($port, isset($this->registeredPorts) ? $this->registeredPorts : [])) {
                $this->registeredPorts[] = $port;
            } else {
                return $this->getAvailablePort();
            }
        } else {
            return $this->getAvailablePort($count + 1, $retryLimit);
        }

        return $port;
    }

    private function getShortClassName(): string
    {
        return (new ReflectionClass($this))->getShortName();
    }

    private function getDocumentBodyHtml(): string
    {
        return $this->client->executeScript('return document.body.innerHTML;');
    }

    public function testWidgetHandlerExecutionWithoutFormThrowsException(): void
    {
        $this->expectException(JavascriptErrorException::class);

        $this->client->executeScript("registerRequiredHandler();");
    }

    public function testWidgetHandlerExecutionWithFormSucceeds(): void
    {
        $this->client->executeScript("registerRequiredHandler(document.createElement('form'));");
        $this->assertNotEmpty($this->getDocumentBodyHtml());
    }

    public function testWidgetHandlerExecutionWithoutCaptchaResponsePreventsSubmission(): void
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

            registerRequiredHandler(widgetContainerWithoutCaptchaResponse.childNodes[0]);
            submitButton.click();
EOF);
        $this->assertNotEmpty($this->getDocumentBodyHtml());
    }

    public function testWidgetHandlerExecutionWithEmptyCaptchaResponsePreventsSubmission(): void
    {
        $this->client->executeScript(<<<EOF
            let widgetContainerWithEmptyCaptchaResponse = document.createElement('form');
            widgetContainerWithEmptyCaptchaResponse.setAttribute('action', 'about:blank');
            widgetContainerWithEmptyCaptchaResponse.setAttribute('method', 'GET');

            let emptyCaptchaResponse = document.createElement('input');
            emptyCaptchaResponse.setAttribute('type', 'hidden');
            emptyCaptchaResponse.setAttribute('name', 'cf-turnstile');

            let submitButton = document.createElement('input');
            submitButton.setAttribute('type', 'submit');
            submitButton.setAttribute('value', 'Submit');

            widgetContainerWithEmptyCaptchaResponse.append(emptyCaptchaResponse);
            widgetContainerWithEmptyCaptchaResponse.append(submitButton);
            document.body.append(widgetContainerWithEmptyCaptchaResponse);

            registerRequiredHandler(widgetContainerWithEmptyCaptchaResponse.childNodes[0]);
            submitButton.click();
EOF);
        $this->assertNotEmpty($this->getDocumentBodyHtml());
    }

    public function testWidgetHandlerExecutionWithCaptchaResponseApprovesSubmission(): void
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

            registerRequiredHandler(widgetContainerWithCaptchaResponse.childNodes[0]);
            submitButton.click();
EOF);
        $this->client->waitForElementToNotContain('body', $this->getShortClassName());
        $this->assertEmpty($this->getDocumentBodyHtml());
    }

    protected static function getKernelClass(): string
    {
        return ValidBundleTestingKernel::class;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        static::$class = null;
    }
}
