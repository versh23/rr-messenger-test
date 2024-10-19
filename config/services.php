<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\RoadRunnerMessenger\LoggerReporter;
use App\RoadRunnerMessenger\RoadRunnerJobWorker;
use App\RoadRunnerMessenger\Transport\RoadRunnerTransportFactory;
use Spiral\Goridge\RPC\RPC;
use Spiral\Messenger\Sender\RoadRunnerSender;
use Spiral\Messenger\Serializer\BodyContext;
use Spiral\Messenger\Serializer\HeaderContext;
use Spiral\Messenger\Serializer\Serializer;
use Spiral\Messenger\Serializer\StampSerializer;
use Spiral\RoadRunner\Jobs\Consumer;
use Spiral\RoadRunner\Jobs\Jobs;
use Symfony\Component\Serializer\SerializerInterface;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $container->services()
        ->load('App\\', '../src/')
        ->exclude([
            '../src/DependencyInjection/',
            '../src/RoadRunnerMessenger/',
            '../src/Entity/',
            '../src/Kernel.php',
        ])->autoconfigure()->autowire()
    ;

    $services = $container->services();

    $services->set('rr.rpc', RPC::class)
        ->factory([null, 'create'])
        ->arg(0, env('RR_RPC'));

    $services->set('rr.jobs', Jobs::class)
        ->arg(0, service('rr.rpc'));

    $services->set('rr.reporter', LoggerReporter::class)
        ->arg(0, service('logger')->ignoreOnInvalid());

    $services->set('rr.body-context', BodyContext::class)
        ->arg(0, 'json');

    $services->set('rr.header-context', HeaderContext::class)
        ->arg(0, 'json');

    $services->set('rr.stamp-serializer', StampSerializer::class)
        ->arg(0, service(SerializerInterface::class))
        ->arg(1, service('rr.header-context'))
    ;

    $services->set('rr.serializer', Serializer::class)
        ->arg(0, service(SerializerInterface::class))
        ->arg(1, service('rr.stamp-serializer'))
        ->arg(2, service('rr.body-context'))
    ;

    $services->set('rr.sender', RoadRunnerSender::class)
        ->arg(0, service('rr.jobs'))
        ->arg(1, service('rr.serializer'))
        ->arg(2, service('rr.reporter'))
    ;

    $services->set(RoadRunnerTransportFactory::class)
        ->arg(0, service('rr.sender'))
        ->tag('messenger.transport_factory');

    $services->set('rr.consumer', Consumer::class);

    $services->set(RoadRunnerJobWorker::class)
        ->arg(0, service('rr.consumer'))
        ->arg(1, service('rr.serializer'))
        ->arg(2, service('rr.stamp-serializer'))
        ->arg(3, service('messenger.routable_message_bus'))
        ->arg(4, service('rr.reporter'))
        ->tag('console.command')
    ;
};
