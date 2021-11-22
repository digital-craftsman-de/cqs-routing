# Handler wrapper

Handler wrappers are components that allow executation of code before (`prepare`), after success (`then`), after error (`catch`) and after in both cases (`finally`). Each method has its own priority with which it's executed in relation to other handler wrappers. Through this priority it's possible to have the `prepare` method be called first for one handler wrapper but the `finally` method be triggered last. The priority mirrors the event listener logic from Symfony in that it's `0` as default and can usually range from `-256` to `256`. 

With handle wrappers it's possible to implement automatic transaction rollbacks, locking of requests or silent exceptions. All things that are generally part of an appliaction layer and not part of the domain.

For now there are no built-in handler wrappers because they are highly dependant of the domain implementation and / or depent on external libraries.

We still go through a few examples to explain how they are used. 

## Automatic rollback of doctrine transactions

The logic here is pretty simple: Before running a handler, we start a new transation. When everything worked we simply commit it. And when there was any exception, we roll back the transaction.

```php
<?php

declare(strict_types=1);

namespace App\CQRS\HandlerWrapper;

use DigitalCraftsman\CQRS\Command\Command;
use DigitalCraftsman\CQRS\HandlerWrapper\HandlerWrapperInterface;
use DigitalCraftsman\CQRS\Query\Query;
use Doctrine\DBAL\Connection;

final class ConnectionTransactionWrapper implements HandlerWrapperInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    /** @param null $parameters */
    public function prepare(
        Command | Query $dto,
        mixed $parameters,
    ): void {
        $this->connection->beginTransaction();
    }

    /** @param null $parameters */
    public function catch(
        Command | Query $dto,
        mixed $parameters,
        \Exception $exception,
    ): ?\Exception {
        if ($this->connection->isTransactionActive()) {
            $this->connection->rollBack();
        }

        return $exception;
    }

    /** @param null $parameters */
    public function then(
        Command | Query $dto,
        mixed $parameters,
    ): void {
        $this->connection->commit();
    }

    /** @param null $parameters */
    public function finally(
        Command | Query $dto,
        mixed $parameters,
    ): void {
        // Nothing to do
    }

    // Priorities

    public static function preparePriority(): int
    {
        return 50;
    }

    public static function catchPriority(): int
    {
        return 50;
    }

    public static function thenPriority(): int
    {
        return 50;
    }

    public static function finallyPriority(): int
    {
        return 0;
    }
}
```

## Silence exceptions

When the `catch` method of a handler wrapper is executed, the exception is returned at the end. If it's the last handler wrapper that should handle it, it must return `null` instead.

This logic is what is used with our silent exception wrapper. With it, exceptions are checked against a specific exception list (defined through a parameter as part of the route). When an exception matches, the exception is not returned and therefore doesn't bubble up the chain.

```php
<?php

declare(strict_types=1);

namespace App\CQRS\HandlerWrapper;

use DigitalCraftsman\CQRS\Command\Command;
use DigitalCraftsman\CQRS\HandlerWrapper\HandlerWrapperInterface;
use DigitalCraftsman\CQRS\Query\Query;

final class SilentExceptionWrapper implements HandlerWrapperInterface
{
    /** @param array<int, string> $parameters */
    public function prepare(
        Command | Query $dto,
        mixed $parameters,
    ): void {
        // Nothing to do
    }

    /** @param array<int, string> $parameters Exception class strings to be swallowed */
    public function catch(
        Command | Query $dto,
        mixed $parameters,
        \Exception $exception,
    ): ?\Exception {
        // Catch exception which should be handled silently
        if (in_array(get_class($exception), $parameters, true)) {
            return null;
        }

        return $exception;
    }

    /** @param array<int, string> $parameters */
    public function then(
        Command | Query $dto,
        mixed $parameters,
    ): void {
        // Nothing to do
    }

    /** @param array<int, string> $parameters */
    public function finally(
        Command | Query $dto,
        mixed $parameters,
    ): void {
        // Nothing to do
    }

    // Priorities

    public static function preparePriority(): int
    {
        return 0;
    }

    public static function catchPriority(): int
    {
        return -100;
    }

    public static function thenPriority(): int
    {
        return 0;
    }

    public static function finallyPriority(): int
    {
        return 0;
    }
}
```

This might be useful when the flow of a command handler should be stopped, but no error must be shown to the user. As an example, imagine a command to change an email address where the email address hasn't changed. No confirmation email must be send out and no data must be stored. But the user must also not get an error because the email address itself is valid. With this handler wrapper, we can throw a `EmailAddressDidNotChange` exception to exit the flow.

The priority of the `catch` method is set to a low value like `-100` to make sure it's executed last and doesn't prevent another handler wrapper that for example rolls back a doctrine transation.

Handler wrappers in a route are not defined like other components with just the class names, but instead as `HandlerWrapperConfiguration`. They still contain the class of the implementation but additionally can define parameters that can be used in the handler wrapper.

```php
'routePayload' => Configuration::routePayload(
    handlerWrapperConfigurations: [
        new HandlerWrapperConfiguration(
            handlerWrapperClass: SilentExceptionWrapper::class,
            parameters: [
                EmailAddressDidNotChange::class,
            ],
        ),
    ],
),
```

## Request locking

There are some requests which can't be run in parallel. For such requests we can use the symfony lock bundle and a custom handler wrapper.

With our example we create and acquire a lock depending on the user id. So we prevent that a user is able to create multiple news entries at the same time.

The priority of `prepare` is very high and the priority of `finally` is very low. This way we can make sure that the lock is created before a doctrine transation is created and released after the transaction is commited or rolled back. So we know that those handler wrappers don't interfere with each other. 

```php
<?php

declare(strict_types=1);

namespace App\Domain\News\WriteSide\CreateNewsArticle;

use App\Service\Lock\LockService;
use DigitalCraftsman\CQRS\Command\Command;
use DigitalCraftsman\CQRS\HandlerWrapper\HandlerWrapperInterface;
use DigitalCraftsman\CQRS\Query\Query;
use Symfony\Component\Lock\LockInterface;

final class CreateNewsArticleHandlerWrapper implements HandlerWrapperInterface
{
    private ?LockInterface $lock = null;

    public function __construct(
        private LockService $lockService,
    ) {
    }

    /** @param CreateNewsArticleCommand $dto */
    public function prepare(
        Command | Query $dto,
        mixed $parameters,
    ): void {
        $this->lock = $this->lockService->createLock((string) $dto->userId);
        $this->lock->acquire(true);
    }

    /** @param null $parameters */
    public function catch(
        Command | Query $dto,
        mixed $parameters,
        \Exception $exception,
    ): ?\Exception {
        // Nothing to do
        return $exception;
    }

    /** @param null $parameters */
    public function then(
        Command | Query $dto,
        mixed $parameters,
    ): void {
        // Nothing to do
    }

    /** @param null $parameters */
    public function finally(
        Command | Query $dto,
        mixed $parameters,
    ): void {
        $this->lock->release();
    }

    // Priorities

    public static function preparePriority(): int
    {
        return 200;
    }

    public static function catchPriority(): int
    {
        return 0;
    }

    public static function thenPriority(): int
    {
        return 0;
    }

    public static function finallyPriority(): int
    {
        return -200;
    }
}
```
