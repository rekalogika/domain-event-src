# rekalogika/domain-event

An implementation of domain event pattern for Symfony & Doctrine.

Full documentation is available at
[rekalogika.dev/domain-event](https://rekalogika.dev/domain-event).

## What is a Domain Event?

A domain event is simply a regular event like you would normally use with
Symfony's EventDispatcher. The difference is that a domain event represents
something that has happened in your domain. It has a name that is meaningful to
the underlying business that the domain represents. A domain event is usually
dispatched by your entities, as opposed to being dispatched from your
controllers or other services.

## Why Use Domain Events?

A domain event represents a business event that has happened. It is a good way
to model the business requirements that say "when something happens, do this".

A domain event is raised by the part of your code where the event is actually
happening. Different part of your application might call the same method on an
entity. In some cases, the method is called indirectly, and the caller has no
idea that it is being called. By using domain events, the event will be
dispatched in all the cases. No need to make sure to dispatch the event from all
the different places where the method is called.

The application layer (controllers, services) can tell an entity to do
something, but it cannot reliably know if the action is actually performed, or
if an additional action is performed. A controller or a service can ask
`$bookshelf->removeBook($book)`, but only the `$bookshelf` knows if the book was
actually removed. And if the event actually happened, the entity can tell the
world about it by recording a `BookRemoved` event.

Some problems might tempt you to inject a service into your entity. With domain
events, you can avoid that. You can make your entity dispatch an event, and set
up a listener to react to that event. The relevant services can then correctly
act on your entity, instead of the other way around.

## Synopsis

```php
//
// The event
//

final readonly class PostPublished
{
    public function __construct(public string $postId) {}
}

//
// The entity
//

use Rekalogika\Contracts\DomainEvent\DomainEventEmitterInterface;
use Rekalogika\Contracts\DomainEvent\DomainEventEmitterTrait;

class Post implements DomainEventEmitterInterface
{
    use DomainEventEmitterTrait;
    
    // ...

    public function setStatus(string $status): void
    {
        $originalStatus = $this->status;
        $this->status = $status;

        // records the published event if the new status is published and it
        // is different from the original status

        if ($status === 'published' && $originalStatus !== $status) {
            $this->recordEvent(new PostPublished($this->id));
        }
    }

    // ...
}

//
// The listener
//

use Psr\Log\LoggerInterface;
use Rekalogika\Contracts\DomainEvent\Attribute\AsPostFlushDomainEventListener;

class PostEventListener
{
    public function __construct(private LoggerInterface $logger) {}

    // will be called after the post is published and the entity manager is
    // flushed
    
    #[AsPostFlushDomainEventListener]
    public function onPostPublished(PostPublished $event) {
        $postId = $event->postId;

        $this->logger->info("Post $postId has been published.");
    }
}

//
// The caller
//

use Doctrine\ORM\EntityManagerInterface;

/** @var Post $post */
/** @var EntityManagerInterface $entityManager */

$post->setStatus('published');
$entityManager->flush();

// the event will be dispatched after the flush above, afterwards the listener
// above will be called, sending a message to the logger
```

## Features

* Works out of the box. No configuration is required for basic features.
* Simple, unopinionated architecture. Uses plain event objects, and doesn't
  require much from your domain entities.
* Uses standard Symfony's EventDispatcher, with the same dispatching semantics
  & listener registrations.
* Transaction support.
* Works with multiple entity managers.
* Multiple events considered identical are dispatched only once.
* Four listening strategies: immediate, pre-flush, post-flush, and event bus.
* Uses Symfony Messenger as the event bus implementation.
* Utilizes the transactional outbox pattern when publishing events to the event
  bus to guarantee consistency and delivery.
* Utilizes Symfony Scheduler to relay undelivered events to the event bus.
* Does not require you to change how you work with entities.
* Should work everywhere without any change: in controllers, message handlers,
  command line, etc.
* Separated contracts & framework. Useful for enforcing architectural
  boundaries. Your domain doesn't have to depend on the framework.
* Symfony Profiler integration. Debug your events in the profiler's events
  panel.

## To Do

* Support for Doctrine MongoDB ODM.
* Support event inheritance.
* Deprecate `__remove()` and use a method tagged with an attribute instead.

## Installation

Ensure that Symfony Flex is enabled (it is enabled by default). Open a command
console, enter your project directory and execute:

```bash
composer require rekalogika/domain-event
```

## Documentation

[rekalogika.dev/domain-event](https://rekalogika.dev/domain-event)

## License

MIT

## Contributing

The `rekalogika/domain-event` repository is a read-only repo split from the main
repo. Issues and pull requests should be submitted to the
[rekalogika/domain-event-src](https://github.com/rekalogika/domain-event-src)
monorepo.
