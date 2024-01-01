<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Test\Unit\Client;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Exception\InvalidArgumentException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
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
        /** @phpstan-ignore-next-line */
        $client = new CloudflareTurnstileClient(new MockHttpClient(new MockResponse(json_encode([
            'success' => true
        ]))), '', []);

        $this->assertTrue($client->verify('<captcha_response>'));
    }

    /**
     * @dataProvider emptyCaptchaResponses
     */
    public function testEmptyCaptchaResponseDirectlyReturnsFailureOnVerification($emptyCaptchaResponse): void
    {
        $client = new CloudflareTurnstileClient($this->createMock(HttpClientInterface::class), '', []);

        $this->assertFalse($client->verify($emptyCaptchaResponse));
    }

    /**
     * @dataProvider unsuccessfulClientResponses
     */
    public function testUnsuccessfulClientResponseVerificationWithoutOptions(array $unsuccessfulClientResponse): void
    {
        /** @phpstan-ignore-next-line */
        $client = new CloudflareTurnstileClient(new MockHttpClient(new MockResponse(json_encode($unsuccessfulClientResponse))), '', []);

        $this->assertFalse($client->verify('<captcha_response>'));
    }

    public function testInvalidClientResponseVerificationThrowsException(): void
    {
        $this->expectException(CloudflareTurnstileApiException::class);

        $mockedHttpClient = $this->createMock(HttpClientInterface::class);
        $mockedHttpClient->method('request')
            ->willThrowException($this->createMock(ExceptionInterface::class));

        $client = new CloudflareTurnstileClient($mockedHttpClient, '', []);

        $client->verify('<captcha_response>');
    }

    /**
     * Checks that client options and custom verification options are correctly merged
     *
     * @dataProvider clientOptionsWithVerificationOptions
     */
    public function testClientOptionsWithVerificationOptions(array $clientHttpOptions, array $httpVerificationOptions, array $expectedHttpOptions): void
    {
        $client = new CloudflareTurnstileClient($this->createMock(HttpClientInterface::class), '', $clientHttpOptions);

        $this->assertSame($expectedHttpOptions, $client->handleOptions($httpVerificationOptions));
    }

    /**
     * @dataProvider clientOptionsWithVerificationOptions
     */
    public function testSuccessfulClientResponseWithVerificationOptions(array $clientHttpOptions, array $verificationHttpOptions): void
    {
        /** @phpstan-ignore-next-line */
        $client = new CloudflareTurnstileClient(new MockHttpClient(new MockResponse(json_encode([
            'success' => true
        ]))), '', $clientHttpOptions);

        $this->assertTrue($client->verify('<captcha_response>', $verificationHttpOptions));
    }

    /**
     * @dataProvider clientOptionsWithVerificationOptions
     */
    public function testUnsuccessfulClientResponseWithVerificationOptions(array $clientHttpOptions, array $httpVerificationOptions): void
    {
        /** @phpstan-ignore-next-line */
        $client = new CloudflareTurnstileClient(new MockHttpClient(new MockResponse(json_encode([
            'success' => false
        ]))), '', $clientHttpOptions);

        $this->assertFalse($client->verify('<captcha_response>', $httpVerificationOptions));
    }

    /**
     * Checks that client options and custom verification options arrays (with spread operator) are correctly merged
     *
     * @dataProvider clientOptionsWithVerificationOptionsCombinations
     */
    public function testClientOptionsWithVerificationOptionsCombinations(array $clientHttpOptions, array $firstHttpVerificationOptions, array $secondHttpVerificationOptions, array $expectedHttpOptions): void
    {
        $client = new CloudflareTurnstileClient($this->createMock(HttpClientInterface::class), '', $clientHttpOptions);

        $this->assertSame($expectedHttpOptions, $client->handleOptions($firstHttpVerificationOptions, $secondHttpVerificationOptions));
    }

    /**
     * @dataProvider invalidHttpOptions
     */
    public function testInvalidClientHttpOptionsThrowsException(array $clientHttpOptions): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CloudflareTurnstileClient($this->createMock(HttpClientInterface::class), '', $clientHttpOptions);
    }

    /**
     * @dataProvider invalidHttpOptions
     */
    public function testInvalidProvidedHttpOptionsThrowsException(array $providedHttpOptions): void
    {
        $this->expectException(InvalidArgumentException::class);

        $client = new CloudflareTurnstileClient($this->createMock(HttpClientInterface::class), '', []);

        $client->handleOptions($providedHttpOptions);
    }

    /**
     * @dataProvider invalidHttpOptions
     */
    public function testInvalidClientVerificationOptionsThrowsException(array $clientVerificationOptions): void
    {
        $this->expectException(CloudflareTurnstileApiException::class);

        $client = new CloudflareTurnstileClient($this->createMock(HttpClientInterface::class), '', []);

        $client->verify('<captcha_response>', $clientVerificationOptions);
    }

    public function emptyCaptchaResponses(): iterable
    {
        yield [''];
        yield [false];
        yield [null];
    }

    public function unsuccessfulClientResponses(): iterable
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

    public function clientOptionsWithVerificationOptions(): iterable
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
                'max_duration' => 3
            ],
            [
                'body' => [],
                'timeout' => 1,
                'max_duration' => 3
            ]
        ];

        yield [
            [
                'body' => [],
                'timeout' => 1,
                'max_duration' => 3
            ],
            [],
            [
                'body' => [],
                'timeout' => 1,
                'max_duration' => 3
            ]
        ];

        yield [
            [
                'body' => [],
                'timeout' => 1,
            ],
            [
                'max_duration' => 3
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
                'max_duration' => 3
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
                'max_duration' => 3
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
                'timeout' => 2
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

    public function clientOptionsWithVerificationOptionsCombinations(): iterable
    {
        yield [
            [],
            [],
            [],
            []
        ];

        yield [
            [],
            [],
            [
                'max_duration' => 2
            ],
            [
                'max_duration' => 2,
            ]
        ];

        yield [
            [
                'max_duration' => 2
            ],
            [],
            [],
            [
                'max_duration' => 2,
            ]
        ];

        yield [
            [],
            [
                'timeout' => 1
            ],
            [
                'max_duration' => 2
            ],
            [
                'timeout' => 1,
                'max_duration' => 2,
            ]
        ];

        yield [
            [
                'timeout' => 1
            ],
            [],
            [
                'max_duration' => 2
            ],
            [
                'timeout' => 1,
                'max_duration' => 2,
            ]
        ];

        yield [
            [
                'body' => [
                    'test' => true
                ]
            ],
            [
                'timeout' => 1
            ],
            [
                'max_duration' => 2
            ],
            [
                'body' => [
                    'test' => true
                ],
                'timeout' => 1,
                'max_duration' => 2,
            ]
        ];

        yield [
            [
                'body' => [
                    'test' => true
                ]
            ],
            [
                'timeout' => 1
            ],
            [
                'max_duration' => 2
            ],
            [
                'body' => [
                    'test' => true
                ],
                'timeout' => 1,
                'max_duration' => 2,
            ]
        ];

        yield [
            [],
            [
                'body' => [],
                'timeout' => 1,
                'max_duration' => 3
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
                    'test' => true
                ],
                'timeout' => 1,
                'max_duration' => 1
            ],
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

    public function invalidHttpOptions(): iterable
    {
        yield [
            [
                'unknown_option' => null
            ]
        ];

        yield [
            [
                'timeout' => 1,
                'unknown_option' => null
            ]
        ];

        yield [
            [
                'timeout' => 1,
                'max_duration' => 1,
                'unknown_option' => null
            ]
        ];
    }
}
