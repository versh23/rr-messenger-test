<?php

namespace App\RoadRunnerMessenger\Transport;

use Spiral\Messenger\Sender\RoadRunnerSender;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

final readonly class RoadRunnerTransportFactory implements TransportFactoryInterface
{
    public function __construct(
        private RoadRunnerSender $roadRunnerSender,
    ) {
    }

    public function createTransport(
        #[\SensitiveParameter] string $dsn,
        array $options,
        SerializerInterface $serializer,
    ): TransportInterface {
        return new RoadRunnerTransport($this->roadRunnerSender);
    }

    public function supports(#[\SensitiveParameter] string $dsn, array $options): bool
    {
        return str_starts_with($dsn, 'rr-jobs://');
    }
}
