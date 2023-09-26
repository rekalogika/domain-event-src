# rekalogika/domain-event

A domain event pattern implementation for Symfony & Doctrine.

## Features

* Works out of the box. No configuration is required.
* Simple, unopinionated architecture. Uses plain event objects, and doesn't
  require much from your domain entities.
* Uses standard Symfony's event dispatcher, with the same dispatching semantics
  & listener registrations.
* Three dispatching strategies: pre-flush, post-flush, and immediate.
* In pre-flush or post-flush modes, multiple events considered identical are
  dispatched only once.
* Does not require you to change how you work with entities, most of the time.
* Should work everywhere without any change: in controllers, message handlers,
  command line, etc.
* Separated contracts & framework. Useful for enforcing architectural
  boundaries. Your domain doesn't have to depend on the framework.

## Documentation

[rekalogika.dev/domain-event](https://rekalogika.dev/domain-event).

## License

MIT

## Contributing

The `rekalogika/domain-event` repository is a read-only repo split from the main
repo. Issues and pull requests should be submitted to the
[rekalogika/domain-event-src](https://github.com/rekalogika/domain-event-src)
monorepo.
