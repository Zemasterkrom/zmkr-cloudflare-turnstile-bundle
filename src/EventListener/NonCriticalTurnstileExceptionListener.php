<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Zemasterkrom\CloudflareTurnstileBundle\Exception\CloudflareTurnstileException;

/**
 * Exception handler that handles non-critical Cloudflare errors and stops them from propagating
 */
class NonCriticalTurnstileExceptionListener
{
    private LoggerInterface $logger;

    /**
     * @param LoggerInterface $logger The logger to record exception information.
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Handles non-critical exceptions thrown during the application's Cloudflare Turnstile verification.
     *
     * @param ExceptionEvent $event The exception event triggered during the request handling.
     */
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if ($exception instanceof CloudflareTurnstileException && $exception->getCode() === CloudflareTurnstileException::NON_CRITICAL_ERROR) {
            $this->logger->error($exception);
            $event->stopPropagation();
        }
    }
}
