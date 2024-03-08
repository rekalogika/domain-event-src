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

use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\Tools\ToolEvents;
use Rekalogika\DomainEvent\Outbox\EventListener\PostGenerateSchemaListener;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    // $services
    //     ->set(
    //         'rekalogika.domain_event.outbox.event_listener.load_class_metadata',
    //         PostGenerateSchemaListener::class
    //     )
    //     // ->args([
    //     //     '$attributeDriver' => service('rekalogika.domain_event.outbox.doctrine.driver'),
    //     // ])
    //     ->tag('doctrine.event_listener', [
    //         'event' => ToolEvents::postGenerateSchema,
    //     ]);
};
