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

namespace Rekalogika\DomainEvent\Outbox\EventListener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Rekalogika\DomainEvent\Outbox\Entity\OutgoingEvent;

/**
 * @deprecated
 */
class PostGenerateSchemaListener
{
    // /**
    //  * @var ClassMetadata<OutgoingEvent>|null
    //  */
    // private ?ClassMetadata $outgoingEventClassMetadata = null;

    // public function __construct(private AttributeDriver $attributeDriver)
    // {
    // }

    public function postGenerateSchema(GenerateSchemaEventArgs $event): void
    {
        // $entityManager = $event->getEntityManager();
        $schema = $event->getSchema();

        // $entityManager->getMetadataFactory()->setMetadataFor(
        //     OutgoingEvent::class,
        //     $this->getOutgoingEventClassMetadata()
        // );

        // $table = $schema->createTable('rekalogika_event_outbox');
        // $table->addColumn('id', 'bigint', [
        //     'autoincrement' => true,
        //     "unsigned" => true
        // ]);
        // $table->addColumn('event', 'text');
        // $table->setPrimaryKey(['id']);
    }

    // /**
    //  * @return ClassMetadata<OutgoingEvent>
    //  */
    // private function getOutgoingEventClassMetadata(): ClassMetadata
    // {
    //     if (null !== $this->outgoingEventClassMetadata) {
    //         return $this->outgoingEventClassMetadata;
    //     }

    //     $classMetadata = new ClassMetadata(OutgoingEvent::class);
    //     $this->attributeDriver
    //         ->loadMetadataForClass(OutgoingEvent::class, $classMetadata);

    //     return $this->outgoingEventClassMetadata = $classMetadata;
    // }
}
