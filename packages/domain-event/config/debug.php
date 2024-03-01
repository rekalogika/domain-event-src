<?php

declare(strict_types=1);

/*
 * This file is part of rekalogika/domain-event-src package.
 *
 * (c) Priyadi Iman Nurcahyo <https://rekalogika.dev>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

use Rekalogika\DomainEvent\Constants;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services
        ->set(
            'debug.' . Constants::EVENT_DISPATCHER_IMMEDIATE,
            TraceableEventDispatcher::class
        )
        ->decorate(Constants::EVENT_DISPATCHER_IMMEDIATE)
        ->args([
            service('.inner'),
            service('debug.stopwatch'),
            service('logger')->nullOnInvalid(),
            service('.virtual_request_stack')->nullOnInvalid(),
        ])
        ->tag('monolog.logger', ['channel' => 'event'])
        ->tag('kernel.reset', ['method' => 'reset']);

    $services
        ->set(
            'debug.' . Constants::EVENT_DISPATCHER_PRE_FLUSH,
            TraceableEventDispatcher::class
        )
        ->decorate(Constants::EVENT_DISPATCHER_PRE_FLUSH)
        ->args([
            service('.inner'),
            service('debug.stopwatch'),
            service('logger')->nullOnInvalid(),
            service('.virtual_request_stack')->nullOnInvalid(),
        ])
        ->tag('monolog.logger', ['channel' => 'event'])
        ->tag('kernel.reset', ['method' => 'reset']);

    $services
        ->set(
            'debug.' . Constants::EVENT_DISPATCHER_POST_FLUSH,
            TraceableEventDispatcher::class
        )
        ->decorate(Constants::EVENT_DISPATCHER_POST_FLUSH)
        ->args([
            service('.inner'),
            service('debug.stopwatch'),
            service('logger')->nullOnInvalid(),
            service('.virtual_request_stack')->nullOnInvalid(),
        ])
        ->tag('monolog.logger', ['channel' => 'event'])
        ->tag('kernel.reset', ['method' => 'reset']);
};
