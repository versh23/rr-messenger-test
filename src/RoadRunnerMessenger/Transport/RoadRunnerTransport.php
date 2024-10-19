<?php

namespace App\RoadRunnerMessenger\Transport;

use Spiral\Messenger\Sender\RoadRunnerSender;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\TransportInterface;

final readonly class RoadRunnerTransport implements TransportInterface
{
    public function __construct(
        private RoadRunnerSender $roadRunnerSender,
    ) {
    }

    public function send(Envelope $envelope): Envelope
    {
        return $this->roadRunnerSender->send($envelope);
    }

    public function get(): iterable
    {
        throw new \BadMethodCallException('run ./bin/console rr-messenger:worker instead');
    }

    public function ack(Envelope $envelope): void
    {
        throw new \BadMethodCallException('run ./bin/console rr-messenger:worker instead');
    }

    public function reject(Envelope $envelope): void
    {
        throw new \BadMethodCallException('run ./bin/console rr-messenger:worker instead');
    }
}
