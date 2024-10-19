<?php

namespace App\RoadRunnerMessenger;

use Spiral\Exceptions\ExceptionReporterInterface;
use Spiral\Messenger\Dispatcher\TaskState;
use Spiral\Messenger\Serializer\StampSerializer;
use Spiral\Messenger\Stamp\RetryHandlerStamp;
use Spiral\RoadRunner\Jobs\ConsumerInterface;
use Spiral\RoadRunner\Jobs\Task\ReceivedTaskInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\AckStamp;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;
use Symfony\Component\Messenger\Stamp\NoAutoAckStamp;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

#[AsCommand(name: 'rr-messenger:worker')]
final class RoadRunnerJobWorker extends Command
{
    public function __construct(
        private readonly ConsumerInterface $consumer,
        private readonly SerializerInterface $serializer,
        private readonly StampSerializer $stampSerializer,
        private readonly MessageBusInterface $messageBus,
        private readonly ExceptionReporterInterface $reporter,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        trap('start');
        while ($task = $this->consumer->waitTask()) {
            trap($task);

            // TODO: use mapper for headers
            $headers = [];
            foreach ($task->getHeaders() as $key => $value) {
                $headers[$key] = $task->getHeaderLine($key);
            }

            $envelope = $this->serializer->decode([
                'body' => $task->getPayload(),
                'headers' => $headers,
            ])->with(
                new TransportMessageIdStamp($task->getId()),
            );

            try {
                $this->handleMessage($task, $envelope);
                trap('handled', $envelope);
            } catch (\Throwable $e) {
                $this->reporter->report($e);
                $task->nack($e);
                trap('error', $e);
            }

            trap('tick');

            // TODO: reset services
            // $finalizer->finalize(terminate: false);
        }

        trap('stop');

        return Command::SUCCESS;
    }

    /**
     * @throws ExceptionInterface
     */
    private function handleMessage(ReceivedTaskInterface $task, Envelope $envelope): void
    {
        $state = new TaskState($this->stampSerializer, $task);
        $this->messageBus->dispatch($envelope, [
            new ConsumedByWorkerStamp(),
            new AckStamp($state->ack(...)),
            new RetryHandlerStamp($state->retry(...)),
            new ReceivedStamp('roadrunner'),
        ]);

        $noAutoAckStamp = $envelope->last(NoAutoAckStamp::class);
        if (!$state->isProcessed() && !$noAutoAckStamp) {
            $state->ack($envelope);
        }
    }
}
