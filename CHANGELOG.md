# Changelog

## 2.5.2

* feat(outbox): remove duplicate events in a single invocation
* fix: allows `pop()` transactions in the current transaction scope
* fix: for some reason, sometimes `kernel.reset` event gets executed before
  `WorkerMessageHandledEvent`, and therefore the relayer does not get executed.
* deps: allow doctrine/persistence-4

## 2.5.1

* chore: static analysis fix
* fix: change outbox primary key to bigint
* feat: also listen to `WorkerMessageHandledEvent` for relaying messages
* feat: CLI relay all messages, not just the first 100

## 2.5.0

* feat: PHP 8.4 compatibility
* build: improve monorepo build

## 2.4.0

* fix(OutboxMessage): fix ID generation strategy
* build: include github-actions in dependabot
* fix: remove deprecated service definition
* fix: deprecation warning
* dep: bump psalm

## 2.3.4

* fix: collect events at the beginning of `dispatchPostFlushDomainEvents()`, fix
  manual dispatching postFlush events without preFlush.

## 2.3.3

* fix: Should fix `ManagerRegistry::resetManager()`

## 2.3.2

* chore: enable native_function_invocation in php-cs-fixer

## 2.3.1

* fix: invalid version in `composer.json`
* build: validate monorepo in CI
* build: update phpunit

## 2.3.0

* fix: ORM 3 compatibility

## 2.2.3

* fix: commit & rollback without transaction no longer throw an exception

## 2.2.2

* fix: handle case where `ProfilerController` is not available

## 2.2.1

* feat: run message relay on `console.terminate` event

## 2.2.0

* refactor: spin off `DomainEventStore` from `DomainEventAwareEntityManager` for
  better separation of concerns & easy reuse.
* feat: full transaction support for postFlush
* fix: override `transactional()` and `wrapInTransaction()`
* feat: function to relay messages from all entity managers
* feat: add schedule to relay message every 1 hour

## 2.1.0

* feat: Transaction support.
* test: test equatable events.
* feat: add DomainEventAware methods in `DomainEventAwareManagerRegistry`.
* test: enable `use_savepoints` to test nested transactions.
* test: require `symfony/debug-bundle`
* feat: allow resolving manager name from manager instance.
* feat: transactional outbox pattern
* fix: serialized data corruption in database
* fix: `DomainEventDispatchListener` event tagging
* feat: add logging in message relaying
* feat: configuration for outbox
* refactor: use async transport by default
* refactor: removes `messenger_transport` configuration, and let users configure
  the routing configuration as usual

## 2.0.1

* test: Require `symfony/twig-bundle` & `symfony/web-profiler-bundle` for tests
* fix: use non-class service ids to prevent auto wiring
* fix: workaround problem with profiler.

## 2.0.0

* test: Overhaul tests
* fix: Decorates the main Doctrine service IDs, not the aliases.
* fix: Use `callable|array` typehint when decorating event dispatchers. See https://github.com/symfony/symfony/issues/48130
* test: refactor tests
* feat: supports multiple entity managers.
* test: test flush in preflush.
* test: record event inside event listener.
* feat: infinite loop safeguard
* feat: uninstall immediate dispatcher when the kernel is shut down.
* fix: Add `ResetInterface` to `DomainEventAwareManagerRegistry`
* refactor: Merge `DomainEventManager` with `DomainEventAwareEntityManager`
* test: add remove tests.
* refactor: remove unnecessary `collect()` from
  `DomainEventAwareEntityManagerInterface`
* refactor: `DomainEventManagerInterface`
* refactor: move interfaces to top level namespace
* refactor: remove `DomainEventEmitterCollector`
* feat: Now should fully support multiple entity managers.
* refactor: assorted cleanups
* refactor: merge `ObjectManagerDecoratorResolver` to
  `DomainEventAwareManagerRegistry`
* feat: Pre & PostFlush dispatch events now have the entity manager.
* test: Multiple entity managers.

## 1.2.5

* fix: Properly implement `ResetInterface` on applicable services.
* test: Add tests for Symfony 7 & PHP 8.3
* refactor: Refactor exceptions
* feat: Dispatch `DomainEventPostFlushDispatch` or
  `DomainEventPostFlushDispatch` every time a domain event is dispatched.
* feat: Dispatch `DomainEventImmediateDispatch` every time a domain event is
  dispatched.
* refactor: The dispatch events are now dispatched by decorating the event
  dispatchers.

## 1.2.4

* build: Fix monorepo build.

## 1.2.2

* refactor(`ImmediateDomainEventDispatcherInstaller`): remove installation from
  `kernel.request` & `console.command` events.
* test: Require `symfony/framework-bundle`.
* feat: Data collector integration.

## 1.2.1

* build: Bump minimal Symfony version to 6.4.
* build: Update `php-cs-fixer` and config.
* refactor(`ImmediateDomainEventDispatcherInstaller`): now installs only in
  bundle's `boot()`, remove installation from other services.

## 1.2.0

* Supports Symfony 7

## 1.1.9

* php-cs-fixer run
## 1.1.8

* Improve CI
* Remove dev dependency on symfony/uid
* Method signature fixes with earlier Symfony versions

## 1.1.7

* remove unneeded event argument in DomainEventReaper

## 1.1.6

* reap events on console errors & install immediate dispatchers at the beginning of a console command
* fix psalm configuration

## 1.1.5

* fix integration test

## 1.1.4

* fix disabling autodispatch not previously working
* change psalm config to scan everything under packages

## 1.1.3

* lessen version requirements of symfony packages
* add docs about immediate dispatcher installation

## 1.1.2

* Installs immediate dispatcher when ManagerRegistry or EntityManager is initialized
* don't complain if the dispatcher get installed twice

## 1.1.1

* add keywords and fix description
