<?php

/*
 * This file is part of rekalogika/domain-event package.
 *
 * (c) Priyadi Iman Nurcahyo <https://rekalogika.dev>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Rekalogika\DomainEvent\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Rekalogika\DomainEvent\Doctrine\DomainEventAwareEntityManager;
use Rekalogika\DomainEvent\Doctrine\DomainEventAwareManagerRegistry;
use Rekalogika\DomainEvent\DomainEventManager;
use Rekalogika\DomainEvent\Exception\FlushNotAllowedException;
use Rekalogika\DomainEvent\ImmediateDomainEventDispatcherInstaller;
use Rekalogika\DomainEvent\Tests\Event\EntityCreated;
use Rekalogika\DomainEvent\Tests\Event\EntityNameChanged;
use Rekalogika\DomainEvent\Tests\Event\EntityRemoved;
use Rekalogika\DomainEvent\Tests\Event\EquatableEvent;
use Rekalogika\DomainEvent\Tests\Event\NonEquatableEvent;
use Rekalogika\DomainEvent\Tests\EventListener\DomainEventListener;
use Rekalogika\DomainEvent\Tests\EventListener\EquatableEventListener;
use Rekalogika\DomainEvent\Tests\EventListener\FlushingDomainEventListener;
use Rekalogika\DomainEvent\Tests\Model\Entity;
use Rekalogika\DomainEvent\Tests\Service\DomainEventEmitterCollectorStub;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class DomainEventTest extends TestCase
{
    protected EventDispatcher $immediateEventDispatcher;
    protected EventDispatcher $preFlushEventDispatcher;
    protected EventDispatcher $postFlushEventDispatcher;
    protected EventDispatcher $defaultEventDispatcher;
    protected DomainEventManager $domainEventManager;
    protected EntityManagerInterface $entityManager;
    protected ImmediateDomainEventDispatcherInstaller $installer;

    public function setUp(): void
    {
        $this->immediateEventDispatcher = new EventDispatcher();
        $this->preFlushEventDispatcher = new EventDispatcher();
        $this->postFlushEventDispatcher = new EventDispatcher();
        $this->defaultEventDispatcher = new EventDispatcher();

        $this->domainEventManager = new DomainEventManager(
            defaultEventDispatcher: $this->defaultEventDispatcher,
            postFlushEventDispatcher: $this->postFlushEventDispatcher,
            preFlushEventDispatcher: $this->preFlushEventDispatcher,
        );

        $this->installer = new ImmediateDomainEventDispatcherInstaller($this->immediateEventDispatcher);
        $this->installer->install();
    }

    /**
     * @testdox Immediate Domain Event Dispatching
     */
    public function testImmediate(): void
    {
        $listener = new DomainEventListener();

        $this->immediateEventDispatcher->addListener(
            EntityCreated::class,
            [$listener, 'onEntityCreated']
        );
        $this->immediateEventDispatcher->addListener(
            EntityRemoved::class,
            [$listener, 'onEntityRemoved']
        );
        $this->immediateEventDispatcher->addListener(
            EntityNameChanged::class,
            [$listener, 'onEntityNameChanged']
        );

        $this->assertFalse($listener->isEntityRemovedHeard());
        $this->assertFalse($listener->isEntityNameChangedHeard());
        $this->assertFalse($listener->isEntityCreatedHeard());

        $entity = new Entity('foo');
        $this->assertTrue($listener->isEntityCreatedHeard());

        $entity->setName('bar');
        $this->assertTrue($listener->isEntityNameChangedHeard());

        $this->assertFalse($listener->isEntityRemovedHeard());
        $entity->__remove();
        $this->assertTrue($listener->isEntityRemovedHeard());
    }

    /**
     * @testdox Pre-flush & post-flush domain event dispatching
     */
    public function testPrePost(): void
    {
        $preFlushListener = new DomainEventListener();
        $postFlushListener = new DomainEventListener();

        $this->preFlushEventDispatcher->addListener(
            EntityCreated::class,
            [$preFlushListener, 'onEntityCreated']
        );
        $this->preFlushEventDispatcher->addListener(
            EntityRemoved::class,
            [$preFlushListener, 'onEntityRemoved']
        );
        $this->preFlushEventDispatcher->addListener(
            EntityNameChanged::class,
            [$preFlushListener, 'onEntityNameChanged']
        );

        $this->postFlushEventDispatcher->addListener(
            EntityCreated::class,
            [$postFlushListener, 'onEntityCreated']
        );
        $this->postFlushEventDispatcher->addListener(
            EntityRemoved::class,
            [$postFlushListener, 'onEntityRemoved']
        );
        $this->postFlushEventDispatcher->addListener(
            EntityNameChanged::class,
            [$postFlushListener, 'onEntityNameChanged']
        );

        $this->assertFalse($preFlushListener->isEntityRemovedHeard());
        $this->assertFalse($preFlushListener->isEntityNameChangedHeard());
        $this->assertFalse($preFlushListener->isEntityCreatedHeard());

        $this->assertFalse($postFlushListener->isEntityRemovedHeard());
        $this->assertFalse($postFlushListener->isEntityNameChangedHeard());
        $this->assertFalse($postFlushListener->isEntityCreatedHeard());

        // creation

        $entity = new Entity('foo');
        $this->domainEventManager->collect($entity);

        $this->domainEventManager->preFlushDispatch();
        $this->assertTrue($preFlushListener->isEntityCreatedHeard());
        $this->assertFalse($postFlushListener->isEntityCreatedHeard());

        $this->domainEventManager->postFlushDispatch();
        $this->assertTrue($preFlushListener->isEntityCreatedHeard());
        $this->assertTrue($postFlushListener->isEntityCreatedHeard());

        // change

        $this->assertFalse($preFlushListener->isEntityNameChangedHeard());
        $this->assertFalse($postFlushListener->isEntityNameChangedHeard());
        $entity->setName('bar');
        $this->domainEventManager->collect($entity);

        $this->domainEventManager->preFlushDispatch();
        $this->assertTrue($preFlushListener->isEntityNameChangedHeard());
        $this->assertFalse($postFlushListener->isEntityNameChangedHeard());

        $this->domainEventManager->postFlushDispatch();
        $this->assertTrue($preFlushListener->isEntityNameChangedHeard());
        $this->assertTrue($postFlushListener->isEntityNameChangedHeard());

        // remove

        $this->assertFalse($preFlushListener->isEntityRemovedHeard());
        $this->assertFalse($postFlushListener->isEntityRemovedHeard());
        $entity->__remove();
        $this->domainEventManager->collect($entity);

        $this->domainEventManager->preFlushDispatch();
        $this->assertTrue($preFlushListener->isEntityRemovedHeard());
        $this->assertFalse($postFlushListener->isEntityRemovedHeard());

        $this->domainEventManager->postFlushDispatch();
        $this->assertTrue($preFlushListener->isEntityRemovedHeard());
        $this->assertTrue($postFlushListener->isEntityRemovedHeard());
    }

    private function mockEntityManager(): EntityManagerInterface
    {
        // setup collector & entity manager

        $entity = new Entity('foo');
        $collector = new DomainEventEmitterCollectorStub($entity);

        $uow = $this->createMock(UnitOfWork::class);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->method('getUnitOfWork')
            ->willReturn($uow);

        $entityManager = new DomainEventAwareEntityManager(
            $entityManager,
            $this->domainEventManager,
            $this->installer,
            $collector
        );

        return $entityManager;
    }

    /**
     * @testdox Decorated Entity Manager
     */
    public function testEntityManager(): void
    {
        // setup listeners

        $preFlushListener = new DomainEventListener();
        $postFlushListener = new DomainEventListener();

        $this->preFlushEventDispatcher->addListener(
            EntityCreated::class,
            [$preFlushListener, 'onEntityCreated']
        );
        $this->preFlushEventDispatcher->addListener(
            EntityRemoved::class,
            [$preFlushListener, 'onEntityRemoved']
        );
        $this->preFlushEventDispatcher->addListener(
            EntityNameChanged::class,
            [$preFlushListener, 'onEntityNameChanged']
        );

        $this->postFlushEventDispatcher->addListener(
            EntityCreated::class,
            [$postFlushListener, 'onEntityCreated']
        );
        $this->postFlushEventDispatcher->addListener(
            EntityRemoved::class,
            [$postFlushListener, 'onEntityRemoved']
        );
        $this->postFlushEventDispatcher->addListener(
            EntityNameChanged::class,
            [$postFlushListener, 'onEntityNameChanged']
        );

        $entityManager = $this->mockEntityManager();

        // test

        $this->assertFalse($preFlushListener->isEntityCreatedHeard());
        $this->assertFalse($postFlushListener->isEntityCreatedHeard());

        $entityManager->flush();

        $this->assertTrue($preFlushListener->isEntityCreatedHeard());
        $this->assertTrue($postFlushListener->isEntityCreatedHeard());
    }

    public function testFlushInPreflush(): void
    {
        $entityManager = $this->mockEntityManager();

        // setup listeners

        $preFlushListener = new FlushingDomainEventListener($entityManager);

        $this->preFlushEventDispatcher->addListener(
            EntityCreated::class,
            [$preFlushListener, 'onEntityCreated']
        );
        $this->preFlushEventDispatcher->addListener(
            EntityRemoved::class,
            [$preFlushListener, 'onEntityRemoved']
        );
        $this->preFlushEventDispatcher->addListener(
            EntityNameChanged::class,
            [$preFlushListener, 'onEntityNameChanged']
        );

        $entity = new Entity('foo');
        $entity->setName('bar');
        $entity->__remove();

        $this->domainEventManager->collect($entity);
        $this->expectException(FlushNotAllowedException::class);
        $this->domainEventManager->preFlushDispatch();
    }

    /**
     * @testdox Decorated Manager Registry
     */
    public function testManagerRegistry(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $repository = $this->createMock(ObjectRepository::class);

        $managerRegistry->method('getManager')->willReturn($entityManager);
        $managerRegistry->method('getManagerForClass')->willReturn($entityManager);
        $managerRegistry->method('getManagers')->willReturn(['default' => $entityManager]);
        $managerRegistry->method('resetManager')->willReturn($entityManager);
        $managerRegistry->method('getRepository')->willReturn($repository);

        $managerRegistry = new DomainEventAwareManagerRegistry(
            $managerRegistry,
            $this->domainEventManager,
            $this->installer
        );

        $this->assertInstanceOf(
            DomainEventAwareEntityManager::class,
            $managerRegistry->getManager()
        );

        $this->assertInstanceOf(
            DomainEventAwareEntityManager::class,
            $managerRegistry->getManagerForClass(Entity::class)
        );

        $this->assertInstanceOf(
            DomainEventAwareEntityManager::class,
            $managerRegistry->getManager('default')
        );

        $this->assertInstanceOf(
            DomainEventAwareEntityManager::class,
            $managerRegistry->resetManager()
        );

        $this->assertInstanceOf(
            DomainEventAwareEntityManager::class,
            $managerRegistry->resetManager('default')
        );

        $managers = $managerRegistry->getManagers();

        foreach ($managers as $manager) {
            $this->assertInstanceOf(
                DomainEventAwareEntityManager::class,
                $manager
            );
        }
    }

    /**
     * @testdox Equatable vs Non-Equatable Events
     */
    public function testEquatable(): void
    {
        $listener = new EquatableEventListener();

        $this->preFlushEventDispatcher->addListener(
            EquatableEvent::class,
            [$listener, 'onEquatableEvent']
        );

        $this->preFlushEventDispatcher->addListener(
            NonEquatableEvent::class,
            [$listener, 'onNonEquatableEvent']
        );

        $entity = new Entity('foo');
        $entity->equatableCheck();

        $this->domainEventManager->collect($entity);
        $this->domainEventManager->preFlushDispatch();
        $this->domainEventManager->postFlushDispatch();

        $this->assertEquals(3, $listener->getNonEquatableEventHeard());
        $this->assertEquals(1, $listener->getEquatableEventHeard());
    }
}
