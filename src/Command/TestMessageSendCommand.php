<?php

namespace App\Command;

use App\Message\TestMessage;
use Spiral\Messenger\Stamp\PipelineStamp;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(name: 'test-message-send')]
class TestMessageSendCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $bus,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $envelope = $this->bus->dispatch(
            new TestMessage('test'),
            [
                new PipelineStamp('test'),
            ]
        );
        dump($envelope);

        return 0;
    }
}
