<?php

namespace App\MessageHandler;

use App\Message\TestMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class TestHandler
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function __invoke(
        TestMessage $message,
    ) {
        $this->logger->debug('handled', [
            'message' => $message,
        ]);
    }
}
