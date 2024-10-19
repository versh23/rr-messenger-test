<?php

namespace App\RoadRunnerMessenger;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Spiral\Exceptions\ExceptionReporterInterface;

final readonly class LoggerReporter implements ExceptionReporterInterface
{
    private LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger)
    {
        $this->logger = $logger ?? new NullLogger();
    }

    public function report(\Throwable $exception): void
    {
        $this->logger->error(\sprintf(
            '%s: %s in %s at line %s',
            $exception::class,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        ), [
            'exception' => $exception,
        ]);
    }
}
