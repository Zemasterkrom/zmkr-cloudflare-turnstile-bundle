<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Test\Integration\JavaScript;

use PHPUnit\Framework\TestCase;

class CloudflareTurnstileWidgetHandlerTest extends TestCase
{
    public function testWidgetHandlerIsCorrectlyAssociatedInWidgetView(): void
    {
        $javascriptWidgetHandlerVersionedFileName = glob(__DIR__ . '/../../../src/Resources/public/js/zmkr_cloudflare_turnstile_widget_handler*.js');

        if (!$javascriptWidgetHandlerVersionedFileName || count($javascriptWidgetHandlerVersionedFileName) > 1) {
            throw new \RuntimeException('Unable to find unique versioned JavaScript widget handler. Make sure only one associated file is present.');
        }

        $javascriptWidgetHandlerVersionedFileName = basename($javascriptWidgetHandlerVersionedFileName[0]);
        $widgetTemplateContents = file_get_contents(__DIR__ . '/../../../src/Resources/views/zmkr_cloudflare_turnstile_widget.html.twig');

        if (!$widgetTemplateContents) {
            throw new \RuntimeException('Unable to load the Twig template associated to the Cloudflare Turnstile widget');
        }

        $this->assertStringContainsString($javascriptWidgetHandlerVersionedFileName, $widgetTemplateContents);
    }
}
