# rekalogika/domain-event-outbox

Implementation of the transactional outbox pattern on top of the
`rekalogika/domain-event` package.

Full documentation is available at
[rekalogika.dev/domain-event](https://rekalogika.dev/domain-event).

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
    use DomainEventEmitterTrait;
    
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
use Rekalogika\Contracts\DomainEvent\Attribute\AsPublishedDomainEventListener;

class PostEventListener
{
    public function __construct(private LoggerInterface $logger) {}
    
    // highlight-next-line
    #[AsPublishedDomainEventListener]
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

// During the flush above, the event will be recorded in the outbox table in the
// database. Then the message relay service is executed, and will publish the
// events on the event bus. When the event bus announces the event, the listener
// will be executed.
```

## Documentation

[rekalogika.dev/domain-event](https://rekalogika.dev/domain-event).

## License

MIT

## Contributing

The `rekalogika/domain-event-outbox` repository is a read-only repo split from
the main repo. Issues and pull requests should be submitted to the
[rekalogika/domain-event-src](https://github.com/rekalogika/domain-event-src)
monorepo.
