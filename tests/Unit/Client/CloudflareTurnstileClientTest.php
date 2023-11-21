<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Tests\Client;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Zemasterkrom\CloudflareTurnstileBundle\Client\CloudflareTurnstileClient;
use Zemasterkrom\CloudflareTurnstileBundle\Exception\CloudflareTurnstileApiException;

/**
 * Cloudflare Turnstile client test class.
 * Checks that captcha API verification and option management are handled correctly.
 */
class CloudflareTurnstileClientTest extends TestCase
{
    public function testSuccessfulClientResponseVerificationWithoutOptions(): void
    {
        $client = new CloudflareTurnstileClient(new MockHttpClient(new MockResponse(json_encode([
            'success' => true
        ]))), '', []);

        $this->assertTrue($client->verify(''));
    }

    /**
     * @dataProvider unsuccessfulClientResponsesWithoutOptions
     */
    public function testUnsuccessfulClientResponseVerificationWithoutOptions(array $unsuccessfulClientResponse): void
    {
        $client = new CloudflareTurnstileClient(new MockHttpClient(new MockResponse(json_encode($unsuccessfulClientResponse))), '', []);

        $this->assertFalse($client->verify(''));
    }

    public function testInvalidClientResponseVerificationThrowsException(): void
    {
        $this->expectException(CloudflareTurnstileApiException::class);

        $client = new CloudflareTurnstileClient(new MockHttpClient(new MockResponse('')), '', []);

        $this->assertFalse($client->verify(''));
    }

    /**
     * Checks that client options are correctly initialized and that only configured options are retained
     *
     * @dataProvider clientOptionsWithoutCustomVerificationOptions
     */
    public function testClientOptionsWithoutCustomVerificationOptions(array $clientOptions, array $expectedOptions): void
    {
        $client = new CloudflareTurnstileClient($this->createMock(HttpClientInterface::class), '', $clientOptions);

        $this->assertSame($expectedOptions, $client->handleOptions());
    }

    /**
     * @dataProvider clientOptionsWithoutCustomVerificationOptions
     */
    public function testSuccessfulClientResponseVerificationWithoutCustomVerificationOptions(array $clientOptions): void
    {
        $client = new CloudflareTurnstileClient(new MockHttpClient(new MockResponse(json_encode([
            'success' => true
        ]))), '', $clientOptions);

        $this->assertTrue($client->verify(''));
    }

    /**
     * @dataProvider clientOptionsWithoutCustomVerificationOptions
     */
    public function testUnsuccessfulClientResponseVerificationWithoutCustomVerificationOptions(array $clientOptions): void
    {
        $client = new CloudflareTurnstileClient(new MockHttpClient(new MockResponse(json_encode([
            'success' => false
        ]))), '', $clientOptions);

        $this->assertFalse($client->verify(''));
    }

    /**
     * Checks that client options and custom verification options are correctly merged and that only configured options are retained
     *
     * @dataProvider clientOptionsWithCustomVerificationOptions
     */
    public function testClientOptionsWithCustomVerificationOptions(array $clientOptions, array $verificationOptions, array $expectedOptions): void
    {
        $client = new CloudflareTurnstileClient($this->createMock(HttpClientInterface::class), '', $clientOptions);

        $this->assertSame($expectedOptions, $client->handleOptions($verificationOptions));
    }

    /**
     * @dataProvider clientOptionsWithCustomVerificationOptions
     */
    public function testSuccessfulClientResponseWithCustomVerificationOptions(array $clientOptions, array $verificationOptions): void
    {
        $client = new CloudflareTurnstileClient(new MockHttpClient(new MockResponse(json_encode([
            'success' => true
        ]))), '', $clientOptions);

        $this->assertTrue($client->verify('', $verificationOptions));
    }

    /**
     * @dataProvider clientOptionsWithCustomVerificationOptions
     */
    public function testUnsuccessfulClientResponseWithCustomVerificationOptions(array $clientOptions, array $verificationOptions): void
    {
        $client = new CloudflareTurnstileClient(new MockHttpClient(new MockResponse(json_encode([
            'success' => false
        ]))), '', $clientOptions);

        $this->assertFalse($client->verify('', $verificationOptions));
    }

    /**
     * Checks that client options and custom verification options arrays (with spread operator) are correctly merged and that only configured options are retained
     *
     * @dataProvider clientOptionsWithCustomVerificationOptionsArrays
     */
    public function testClientOptionsWithCustomVerificationOptionsArrays(array $clientOptions, array $firstVerificationOptions, array $secondVerificationOptions, array $expectedOptions): void
    {
        $client = new CloudflareTurnstileClient($this->createMock(HttpClientInterface::class), '', $clientOptions);

        $this->assertSame($expectedOptions, $client->handleOptions($firstVerificationOptions, $secondVerificationOptions));
    }

    public function unsuccessfulClientResponsesWithoutOptions(): iterable
    {
        yield [
            [
                'success' => false
            ]
        ];

        yield [
            []
        ];

        yield [
            [
                'test' => true
            ]
        ];
    }

    public function clientOptionsWithoutCustomVerificationOptions(): iterable
    {
        yield [
            [],
            []
        ];

        yield [
            [
                'timeout' => 1
            ],
            [
                'timeout' => 1
            ]
        ];

        yield [
            [
                'timeout' => 1,
                'max_duration' => 2
            ],
            [
                'timeout' => 1,
                'max_duration' => 2
            ]
        ];

        yield [
            [
                'body' => [],
                'timeout' => 1,
                'max_duration' => 2
            ],
            [
                'body' => [],
                'timeout' => 1,
                'max_duration' => 2
            ]
        ];

        yield [
            [
                'body' => [],
                'timeout' => 1,
                'max_duration' => 2,
                'unknown_option' => true
            ],
            [
                'body' => [],
                'timeout' => 1,
                'max_duration' => 2
            ]
        ];
    }

    public function clientOptionsWithCustomVerificationOptions(): iterable
    {
        yield [
            [],
            [],
            []
        ];

        yield [
            [],
            [
                'body' => [],
                'timeout' => 1,
                'max_duration' => 3,
                'unknown_option' => true
            ],
            [
                'body' => [],
                'timeout' => 1,
                'max_duration' => 3
            ]
        ];

        yield [
            [
                'body' => [
                    'test' => false
                ],
                'timeout' => 1,
            ],
            [
                'body' => [
                    'test' => true
                ],
                'timeout' => 2,
                'max_duration' => 3,
                'unknown_option' => true
            ],
            [
                'body' => [
                    'test' => true
                ],
                'timeout' => 2,
                'max_duration' => 3
            ]
        ];

        yield [
            [
                'body' => [
                    'test' => false
                ],
                'timeout' => 1,
                'max_duration' => 1
            ],
            [
                'body' => [
                    'test' => true
                ],
                'timeout' => 2,
                'max_duration' => 3,
                'unknown_option' => true
            ],
            [
                'body' => [
                    'test' => true
                ],
                'timeout' => 2,
                'max_duration' => 3
            ]
        ];

        yield [
            [
                'max_duration' => 1,
            ],
            [
                'max_duration' => 3,
                'body' => [
                    'test' => true
                ],
                'timeout' => 2,
                'unknown_option' => true
            ],
            [
                'max_duration' => 3,
                'body' => [
                    'test' => true
                ],
                'timeout' => 2
            ]
        ];
    }

    public function clientOptionsWithCustomVerificationOptionsArrays(): iterable
    {
        yield [
            [],
            [],
            [],
            []
        ];

        yield [
            [],
            [
                'body' => [],
                'timeout' => 1,
                'max_duration' => 3,
                'unknown_option' => true
            ],
            [
                'body' => [],
                'timeout' => 2,
                'max_duration' => 1
            ],
            [
                'body' => [],
                'timeout' => 2,
                'max_duration' => 1
            ]
        ];

        yield [
            [],
            [
                'body' => [],
                'max_duration' => 2,
            ],
            [
                'body' => [],
                'timeout' => 1,
                'max_duration' => 1
            ],
            [
                'body' => [],
                'max_duration' => 1,
                'timeout' => 1
            ]
        ];

        yield [
            [
                'body' => [
                    'test' => false
                ]
            ],
            [
                'body' => [
                    'test' => false
                ],
                'timeout' => 1
            ],
            [
                'body' => [
                    'test' => true
                ],
                'max_duration' => 1
            ],
            [
                'body' => [
                    'test' => true
                ],
                'timeout' => 1,
                'max_duration' => 1
            ]
        ];

        yield [
            [
                'body' => [
                    'test' => false
                ]
            ],
            [
                'body' => [
                    'test' => false
                ],
                'timeout' => 1,
                'max_duration' => 1,
                'unknown_option' => true
            ],
            [
                'body' => [
                    'test' => true
                ],
                'unknown_option' => true
            ],
            [
                'body' => [
                    'test' => true
                ],
                'timeout' => 1,
                'max_duration' => 1
            ]
        ];

        yield [
            [
                'body' => [
                    'test' => false
                ],
                'timeout' => 1,
                'max_duration' => 1
            ],
            [
                'body' => [
                    'test' => false
                ],
            ],
            [
                'body' => [
                    'test' => true
                ],
                'timeout' => 2,
                'max_duration' => 2
            ],
            [
                'body' => [
                    'test' => true
                ],
                'timeout' => 2,
                'max_duration' => 2
            ]
        ];
    }
}
