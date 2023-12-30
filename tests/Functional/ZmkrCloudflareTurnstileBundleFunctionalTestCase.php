<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Test\Functional;

use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

/**
 * JavaScript CloudflareTurnstile widget handler base test class.
 * Instantiate and setup JavaScript widget handler Cloudflare Tursntile bundle module.
 */
abstract class ZmkrCloudflareTurnstileBundleFunctionalTestCase extends PantherTestCase
{
    protected Client $client;
    protected array $registeredPorts;

    protected function setUp(): void
    {
        $javascriptWidgetHandlerVersionedFileName = glob(__DIR__ . '/../../src/Resources/public/js/zmkr_cloudflare_turnstile_widget_handler*.js');

        if (!$javascriptWidgetHandlerVersionedFileName || count($javascriptWidgetHandlerVersionedFileName) > 1) {
            throw new \RuntimeException('Unable to find unique versioned JavaScript widget handler. Make sure only one associated file is present.');
        }

        $javascriptWidgetHandler = file_get_contents($javascriptWidgetHandlerVersionedFileName[0]);

        if (!$javascriptWidgetHandler) {
            throw new \RuntimeException('Unable to load the JavaScript widget handler');
        }

        /** @var string */
        $widgetHandlerJavascriptFunctions = preg_replace("/^[^\s].+[(].*[)];*$/m", '', $javascriptWidgetHandler);

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

    protected function getAvailablePort(int $count = 1, int $retryLimit = 2): int
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

    protected function getShortClassName(): string
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    protected function getDocumentBodyHtml(): string
    {
        return $this->client->executeScript('return document.body ? document.body.innerHTML : "";') ?? '';
    }
}
