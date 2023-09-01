# rekalogika/domain-event

A [domain event pattern](https://learn.microsoft.com/en-us/dotnet/architecture/microservices/microservice-ddd-cqrs-patterns/domain-events-design-implementation)
implementation for Symfony & Doctrine.

## Features

* Works out of the box. No configuration is required.
* Simple, unopinionated architecture. Uses plain event objects, and doesn't
  require much from your domain entities.
* Uses standard Symfony's event dispatcher, with the same dispatching semantics
  & listener registrations.
* Three modes of dispatching: pre-flush, post-flush, and immediate.
* In pre-flush or post-flush modes, multiple events considered identical are
  dispatched only once.
* Does not require you to change how you work with entities, most of the time.
* Should work everywhere without any change: in controllers, message handlers,
  command line, etc.
* Separated contracts & framework. Useful for enforcing architectural
  boundaries. Your domain doesn't have to depend on the framework.

## Installation

```bash
composer require rekalogika/domain-event
```

## Usage

### Creating Domain Events

Domain events are plain old PHP objects that you create to represent a specific
event happening in your domain.

```php
// our event superclass for the Post object

abstract class AbstractPostEvent
{
    public function __construct(private string $id)
    {
    }

    public function getId(): string
    {
        return $this->id;
    }
}

// our concrete events

final class PostCreated extends AbstractPostEvent
{
}

final class PostChanged extends AbstractPostEvent
{
}

final class PostRemoved extends AbstractPostEvent
{
}
```

### Recording Events

Your emitters (entities) must implement `DomainEventEmitterInterface`.
There is a `DomainEventEmitterTrait` to help you with that. To record events,
you can use the method `recordEvents()` defined in the trait.

```php
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Rekalogika\Contracts\DomainEvent\DomainEventEmitterInterface;
use Rekalogika\Contracts\DomainEvent\DomainEventEmitterTrait;
use Symfony\Component\Uid\UuidV7;

class Post implements DomainEventEmitterInterface
{
    use DomainEventEmitterTrait;

    private string $id;
    private string $title;
    /** @var Collection<int,Comment> */
    private Collection $comments;

    public function __construct(string $title)
    {
        $this->id = new UuidV7();
        $this->title = $title;
        $this->comments = new ArrayCollection();

        $this->recordEvent(new PostCreated($this->id)); // <-- here
    }

    // __remove() is our special method that gets triggered when the entity is
    // going to be removed from the persistence layer
    public function __remove()
    {
        $this->recordEvent(new PostRemoved($this->id)); // <-- here
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;

        $this->recordEvent(new PostChanged($this->id)); // <-- here
    }
}
```

### Listening to Events

To listen to the events, you can use the usual Symfony way of listening to
events. The framework will collect events from persisted entities, and dispatch
them at the end of the `flush()`.

```php
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class PostEventListener
{
    // this method will be invoked after a new Post is persist()-ed & flush()-ed
    public function __invoke(PostCreated $event) {
        $postId = $event->getId();
        // ...
    }
}
```

Alternatively, you can use different attributes to choose a different
dispatching strategy.

```php
use Rekalogika\Contracts\DomainEvent\Attribute\AsImmediateDomainEventListener;
use Rekalogika\Contracts\DomainEvent\Attribute\AsPostFlushDomainEventListener;
use Rekalogika\Contracts\DomainEvent\Attribute\AsPreFlushDomainEventListener;

class PostEventListener
{
    #[AsImmediateDomainEventListener]
    public function immediate(PostCreated $event) {
        // this will run immediately after the entity records the event
    }

    #[AsPreFlushDomainEventListener]
    public function preFlush(PostCreated $event) {
        // this will run when you flush() the new post. before the actual
        // flush()
    }

    #[AsPostFlushDomainEventListener]
    public function postFlush(PostCreated $event) {
        // this will run when you flush() the new post. after the actual
        // flush()
    }
}
```

**Note**:

* `AsEventListener` and `AsPostFlushDomainEventListener` currently have
  identical behavior, but they utilize different event dispatchers. We plan to
  have a different event dispatcher behavior with
  `AsPostFlushDomainEventListener` while keeping `AsEventListener` standard.
* Doing a `flush()` inside a pre-flush listener is not allowed and will result
  in a `FlushNotAllowedException`.

### Equatable Domain Events

A domain event can optionally implement `EquatableDomainEventInterface` which
requires one method `getSignature()`. Two objects with the same signature will
be considered identical by `DomainEventManager` and won't be dispatched twice.

This is useful if your entity is working with a million of related objects. By
implementing `EquatableDomainEventInterface`, you can have your `ObjectChanged`
event dispatched only once, and occupy only a single spot in the memory,
instead of a million times.

```php
use Rekalogika\Contracts\DomainEvent\EquatableDomainEventInterface;

class PostCommentAdded implements EquatableDomainEventInterface
{
    public function __construct(private string $postId)
    {
    }

    public function getSignature(): string
    {
        return sha1(serialize($this));
    }
}

use Rekalogika\Contracts\DomainEvent\DomainEventEmitterInterface;
use Rekalogika\Contracts\DomainEvent\DomainEventEmitterTrait;

class Post implements DomainEventEmitterInterface
{
    use DomainEventEmitterTrait;

    // ...

    public function addComment(string $comment): Comment
    {
        // ...

        // the PostCommentAdded event will only get dispatched once despite of
        // addComment being called multiple times.
        $this->recordEvent(new PostCommentAdded($this->id));
    }
}
```

**Note:** This only applies to pre-flush and post-flush events. Immediate domain
events are dispatched immediately, and there is no chance for the equatable
check to take place.

## Manual Control

### Domain-Event-Aware Entity Manager

To manually manage domain events, you can use
`DomainEventAwareEntityManagerInterface` in place of the regular
`EntityManagerInterface`. It adds several methods to the Entity Manager that you
can use to manage domain event dispatching.

### Manual Dispatching

You can disable automatic dispatching on `flush()` by calling
`setAutoDispatchDomainEvents(false)`.

```php
/** @var DomainEventAwareEntityManagerInterface $entityManager */

$entityManager->setAutoDispatchDomainEvents(false);

// ...

$entityManager->dispatchPreFlushDomainEvents();
$entityManager->flush();
$entityManager->dispatchPostFlushDomainEvents();
```

**Note**: Immediate dispatching is dispatched outside `DomainEventManager` and
`DomainEventAwareEntityManager`, and therefore unaffected by
`setAutoDispatchDomainEvents()`.

### Clearing Events

If the domain event queues are not empty at the end of the request,
`DomainEventManager` will throw `UndispatchedEventsException`. To prevent that
from happening, if you disable auto-dispatch, you need to make sure that you
dispatch both pre-flush and post-flush events as above. Alternatively, you can
clear the events if you don't want them dispatched:

```php
/** @var DomainEventAwareEntityManagerInterface $entityManager */

$entityManager->setAutoDispatchDomainEvents(false);

// ...

$entityManager->flush();
$entityManager->clearDomainEvents();
```

**Note**: In the event of an uncaught error, the framework will automatically
clear undispatched events using the `kernel.exception` event, so in such cases,
you don't have to handle that manually. But if you catch an event that caused
pending events not being dispatched, you need to manually clear the events.

## Best Practice

This explains our best practices that others might find useful, but not strictly
required.

Use UUIDs as identifiers & have the entities generate one for themselves on
instantiation. That means new entities already have an ID before `flush()` and
you can store just the ID in our domain event objects, not the entire object. It
will make the event usable in all cases. It will also result in better logistics
as you can easily pass the events anywhere without alteration.

Use pre-flush events to make alterations to your domain that will be
`flush()`-ed together along with the other changes.

Use post-flush for things that should occur only if the change is successful, like notifications, etc.

## Caveats

* Currently only supports `EntityManager`. Support for other `ObjectManager`s
  is planned.
* It is not aware of explicit transactions yet. You should dispatch the events
  manually if you are using an explicit transaction as described above. In the
  future, we have plans to dispatch post-flush events after the outermost
  `commit()`, and dispatch pre-flush events before every `commit()`.
* It is an inconvenience that Symfony Event Dispatcher does not currently
  support event inheritance. We cannot have a single listener for an entire
  class of domain events, and for example, use it to implement the outbox
  pattern. We plan to fix this in the future.