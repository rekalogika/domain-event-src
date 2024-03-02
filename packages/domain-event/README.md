# rekalogika/domain-event

An implementation of domain event pattern for Symfony & Doctrine.

Full documentation is available at
[rekalogika.dev/domain-event](https://rekalogika.dev/domain-event).

## What is a Domain Event?

A domain event is simply a regular event you would normally use with Symfony's
EventDispatcher. The difference is that a domain event is dispatched by your
entities, as opposed to being dispatched from your controllers or other
services.

## Why Use Domain Events?

A domain event is dispatched from the part of your code where the event is
actually happening. Different part of your application might call the same
method on an entity, directly or indirectly. By using domain events, the event
will be dispatched in all the cases.

The application layer (controllers, services) can tell an entity to do
something, but it cannot reliably know if the action is actually performed. A
controller or a service can ask `$bookshelf->removeBook($book)`, but only the
`$bookshelf` knows if the book was actually removed. And if it actually
happened, it can tell the world about it by dispatching a `BookRemoved` event.

Some problems might tempt you to inject a service into your entity. With domain
events, you can avoid that. Your entity can dispatch an event, and you can set
up a listener to react to that event.

## Features

* Works out of the box. No configuration is required.
* Simple, unopinionated architecture. Uses plain event objects, and doesn't
  require much from your domain entities.
* Uses standard Symfony's event dispatcher, with the same dispatching semantics
  & listener registrations.
* Three dispatching strategies: pre-flush, post-flush, and immediate.
* In pre-flush or post-flush modes, multiple events considered identical are
  dispatched only once.
* Does not require you to change how you work with entities.
* Should work everywhere without any change: in controllers, message handlers,
  command line, etc.
* Separated contracts & framework. Useful for enforcing architectural
  boundaries. Your domain doesn't have to depend on the framework.
* Symfony Profiler integration. Debug your events in the profiler's events
  panel.

## Synopsis

```php
//
// The event
//

final readonly class PostChanged
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
    use DomainEventAwareEntityTrait;
    
    // ...

    public function setTitle(string $title): void
    {
        $this->title = $title;
        // highlight-next-line
        $this->recordEvent(new PostChanged($this->id));
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
    
    // highlight-next-line
    #[AsPostFlushDomainEventListener]
    public function onPostChanged(PostChanged $event) {
        $postId = $event->postId;

        $this->logger->info("Post $postId has been changed.");
    }
}

//
// The caller
//

use Doctrine\ORM\EntityManagerInterface;

/** @var Post $post */
/** @var EntityManagerInterface $entityManager */

$post->setTitle('New title');
$entityManager->flush();
// the event is dispatched after the flush above, and a message will
// appear in the log file
```

## Documentation

[rekalogika.dev/domain-event](https://rekalogika.dev/domain-event).
## Contributing

The `rekalogika/domain-event` repository is a read-only repo split from the main
repo. Issues and pull requests should be submitted to the
[rekalogika/domain-event-src](https://github.com/rekalogika/domain-event-src)
monorepo.
