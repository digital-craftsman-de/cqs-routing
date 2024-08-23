# Handler wrapper examples

**Interface**

```php
interface HandlerWrapperInterface
{
    /**
     * Triggered right before the handler is triggered.
     *
     * @param scalar|array<array-key, scalar|null>|null $parameters
     */
    public function prepare(
        Command|Query $dto,
        Request $request,
        mixed $parameters,
    ): void;

    /**
     * Triggered only if the handler was run without exception.
     *
     * @param scalar|array<array-key, scalar|null>|null $parameters
     */
    public function then(
        Command|Query $dto,
        Request $request,
        mixed $parameters,
    ): void;

    /**
     * Triggered only when an exception occurred while executing the handler.
     * The exception must be returned if it's not explicitly the last exception that should be handled.
     *
     * @param scalar|array<array-key, scalar|null>|null $parameters
     */
    public function catch(
        Command|Query $dto,
        Request $request,
        mixed $parameters,
        \Exception $exception,
    ): ?\Exception;

    public static function preparePriority(): int;

    public static function thenPriority(): int;

    public static function catchPriority(): int;

    /** @param scalar|array<array-key, scalar|null>|null $parameters */
    public static function areParametersValid(mixed $parameters): bool;
}
```

See [position in process](../process.md#handler-wrapper)

## Automatic rollback of doctrine transactions

The logic here is pretty simple: Before running a handler, we start a new transaction. When everything worked we simply commit it. And when there was any exception, we roll back the transaction.

```php
<?php

declare(strict_types=1);

namespace App\CQSRouting\HandlerWrapper;

use DigitalCraftsman\CQSRouting\Command\Command;
use DigitalCraftsman\CQSRouting\HandlerWrapper\HandlerWrapperInterface;
use DigitalCraftsman\CQSRouting\Query\Query;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;

final readonly class ConnectionTransactionWrapper implements HandlerWrapperInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    /** @param null $parameters */
    #[\Override]
    public function prepare(
        Command | Query $dto,
        Request $request,
        mixed $parameters,
    ): void {
        $this->connection->beginTransaction();
    }

    /** @param null $parameters */
    #[\Override]
    public function catch(
        Command | Query $dto,
        Request $request,
        mixed $parameters,
        \Exception $exception,
    ): ?\Exception {
        if ($this->connection->isTransactionActive()) {
            $this->connection->rollBack();
        }

        return $exception;
    }

    /** @param null $parameters */
    #[\Override]
    public function then(
        Command | Query $dto,
        Request $request,
        mixed $parameters,
    ): void {
        $this->connection->commit();
    }

    // Priorities

    #[\Override]
    public static function preparePriority(): int
    {
        return 50;
    }

    #[\Override]
    public static function catchPriority(): int
    {
        return 50;
    }
    
    #[\Override]
    public static function thenPriority(): int
    {
        return 50;
    }
    
    /** @param null $parameters */
    #[\Override]
    public static function areParametersValid(mixed $parameters): bool
    {
        return $parameters === null;
    }
}
```

## Silence exceptions

> ⭐ This handler wrapper is supplied with the bundle.

When the `catch` method of a handler wrapper is executed, the exception is returned at the end. If it's the last handler wrapper that should handle it, it must return `null` instead.

This logic is what is used with our silent exception wrapper. With it, exceptions are checked against a specific exception list (defined through a parameter as part of the route). When an exception matches, the exception is not returned and therefore doesn't bubble up the chain.

The `areParametersValid` method is written in a way that it's not possible to supply classes that are not exceptions. This is validated on cache warmup.

```php
<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\HandlerWrapper;

use DigitalCraftsman\CQSRouting\Command\Command;
use DigitalCraftsman\CQSRouting\HandlerWrapper\HandlerWrapperInterface;
use DigitalCraftsman\CQSRouting\Query\Query;
use Symfony\Component\HttpFoundation\Request;

final readonly class SilentExceptionWrapper implements HandlerWrapperInterface
{
    /** @param array<int, string> $parameters */
    #[\Override]
    public function prepare(
        Command | Query $dto,
        Request $request,
        mixed $parameters,
    ): void {
        // Nothing to do
    }

    /** @param array<int, string> $parameters Exception class strings to be swallowed */
    #[\Override]
    public function catch(
        Command | Query $dto,
        Request $request,
        mixed $parameters,
        \Exception $exception,
    ): ?\Exception {
        // Catch exception which should be handled silently
        if (in_array($exception::class, $parameters, true)) {
            return null;
        }

        return $exception;
    }

    /** @param array<int, string> $parameters */
    #[\Override]
    public function then(
        Command | Query $dto,
        Request $request,
        mixed $parameters,
    ): void {
        // Nothing to do
    }

    // Priorities

    #[\Override]
    public static function preparePriority(): int
    {
        return 0;
    }

    #[\Override]
    public static function catchPriority(): int
    {
        return -100;
    }

    #[\Override]
    public static function thenPriority(): int
    {
        return 0;
    }

    /** @param array<array-key, class-string<\Throwable>> $parameters Needs to be at least one exception class */
    #[\Override]
    public static function areParametersValid(mixed $parameters): bool
    {
        if (!is_array($parameters)) {
            return false;
        }

        if (count($parameters) === 0) {
            return false;
        }

        foreach ($parameters as $exceptionClass) {
            if (!class_exists($exceptionClass)) {
                return false;
            }

            $reflectionClass = new \ReflectionClass($exceptionClass);
            /** @psalm-suppress TypeDoesNotContainType It's possible that someone puts in something other than an exception. */
            if (!$reflectionClass->implementsInterface(\Throwable::class)) {
                return false;
            }
        }

        return true;
    }
}
```

This might be useful when the flow of a command handler should be stopped, but no error must be shown to the user. As an example, imagine a command to change an email address where the email address hasn't changed. No confirmation email must be send out and no data must be stored. But the user must also not get an error because the email address itself is valid. With this handler wrapper, we can throw an `EmailAddressDidNotChange` exception to exit the flow.

The priority of the `catch` method is set to a low value like `-100` to make sure it's executed last and doesn't prevent another handler wrapper (that for example rolls back a doctrine transaction) to be executed.

Handler wrappers are configured with the parameter that is given to the instance on execution. The key of the array must be the handler wrapper class and the parameter is supplied through the value. If there is no relevant parameter use `null` as value.

```php
RouteBuilder::addCommandRoute(
    ...
    handlerWrapperClasses: [
        ConnectionTransactionWrapper::class => null,
        SilentExceptionWrapper::class => [
            EmailAddressDidNotChange::class,
        ],
    ],
));
```

## Request locking

There are some requests which must not be run in parallel. For such requests we can use the Symfony lock bundle and a custom handler wrapper.

With our example we create and acquire a lock depending on the user id. So we prevent that a user is able to create multiple news articles at the same time.

The priority of `prepare` is very high and the priority of `then` is very low. This way we can make sure that the lock is created before a doctrine transaction is created and released after the transaction is committed or rolled back. So we know that those handler wrappers don't interfere with each other.

```php
<?php

declare(strict_types=1);

namespace App\Domain\News\WriteSide\CreateNewsArticle;

use App\Service\Lock\LockService;
use DigitalCraftsman\CQSRouting\Command\Command;
use DigitalCraftsman\CQSRouting\HandlerWrapper\HandlerWrapperInterface;
use DigitalCraftsman\CQSRouting\Query\Query;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Lock\LockInterface;

final class CreateNewsArticleHandlerWrapper implements HandlerWrapperInterface
{
    private ?LockInterface $lock = null;

    public function __construct(
        private readonly LockService $lockService,
    ) {
    }

    /** 
     * @param CreateNewsArticleCommand $dto 
     * @param null                     $parameters
     */
    #[\Override]
    public function prepare(
        Command | Query $dto,
        Request $request,
        mixed $parameters,
    ): void {
        $this->lock = $this->lockService->createLock((string) $dto->userId);
        $this->lock->acquire(true);
    }

    /** @param null $parameters */
    #[\Override]
    public function catch(
        Command | Query $dto,
        Request $request,
        mixed $parameters,
        \Exception $exception,
    ): ?\Exception {
        $this->lock->release();
        
        return $exception;
    }

    /** @param null $parameters */
    #[\Override]
    public function then(
        Command | Query $dto,
        Request $request,
        mixed $parameters,
    ): void {
        $this->lock->release();
    }

    // Priorities

    #[\Override]
    public static function preparePriority(): int
    {
        return 200;
    }

    #[\Override]
    public static function catchPriority(): int
    {
        return 0;
    }

    #[\Override]
    public static function thenPriority(): int
    {
        return -200;
    }
    
    /** @param null $parameters */
    #[\Override]
    public static function areParametersValid(mixed $parameters): bool
    {
        return $parameters === null;
    }
}
```
