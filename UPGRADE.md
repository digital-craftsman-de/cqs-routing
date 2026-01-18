# Upgrade guide

## From 2.0.* to 2.1.0

### Dropped support for Symfony 7.3 and below

Update to at least the LTS version 7.4.

## From 1.1.* to 2.0.0

### Dropped `*Interface` suffix from interfaces

The `*Interface` suffix was dropped from all interfaces. The interfaces are now named `DTOConstructor`, `RequestDecoder`, `ResponseConstructor`, `DTOValidator`, `RequestValidator`, `RequestDataTransformer` and `HandlerWrapper`.

You can use search and replace to rename the `use` and `implements` statements in your implementations.

Before:

```php
use DigitalCraftsman\CQRS\Routing\RequestDecoderInterface;

final readonly class JsonRequestDecoder implements RequestDecoderInterface
```

After:

```php
use DigitalCraftsman\CQRS\Routing\RequestDecoder;

final readonly class JsonRequestDecoder implements RequestDecoder
```

### Dropped support for PHP 8.3

Upgrade to at least PHP 8.4.

### Dropped support for Symfony 6.4

Upgrade to at least Symfony 7.0.

### Renamed configuration for defaults

The defaults were configured through `$cqsRoutingConfig->commandController()` and `$cqsRoutingConfig->queryController()` (or `cqs_routing.command_controller` and `cqs_routing.query_controller` when using YAML). The `Controller` part has been dropped, so it's now `$cqsRoutingConfig->command()` and `$cqsRoutingConfig->query()` (or `cqs_routing.command` and `cqs_routing.query` when using YAML).

Before:

```php
return static function (CqsRoutingConfig $cqsRoutingConfig) {
    $cqsRoutingConfig->queryController()
        ->defaultRequestDecoderClass(JsonRequestDecoder::class)
```

After:

```php
return static function (CqsRoutingConfig $cqsRoutingConfig) {
    $cqsRoutingConfig->query()
        ->defaultRequestDecoderClass(JsonRequestDecoder::class)
```

## From 1.0.* to 1.1.0

### Dropped support for PHP 8.2

Upgrade to at least PHP 8.3. PHP 8.4 is already supported, so you might directly upgrade to PHP 8.4.

## From 0.13.* to 1.0.0

### Renamed package

Replace `digitalcraftsman/cqrs` with `digitalcraftsman/cqs-routing` in `composer.json`.

Then rename the `cqrs.php` to `cqs-routing.php` and rename `CQRSConfig $cqrsConfig` to `CQSRoutingConfig $cqsRoutingConfig` in the configuration.

Before:

```php
return static function (CqrsConfig $cqrsConfig) {
    $cqrsConfig->queryController()
        ->defaultHandlerWrapperClasses([
            ConnectionTransactionWrapper::class => null,
        ]);
```

After:

```php
return static function (CqsRoutingConfig $cqsRoutingConfig) {
    $cqsRoutingConfig->queryController()
        ->defaultHandlerWrapperClasses([
            ConnectionTransactionWrapper::class => null,
        ]);
```

### Renamed namespace

Rename all imports from `DigitalCraftsman\CQRS` to `DigitalCraftsman\CQSRouting`.

It should be possible to use a simple search / replace for that. It's best to do this, before exchanging the packages in the `composer.json`.

## From 0.12.* to 0.13.0

### Upgrade to at least Symfony 6.4

Support for Symfony 5 was dropped, so you have to upgrade to at least Symfony 6.4. You might also directly upgrade to Symfony 7.

## From 0.11.* to 0.12.0

### Switched handler methods

Switched from `handle` to `__invoke` method for `CommandHandler` and `QueryHandler`.

You need to update the methods in your command and query handlers like the following:

Before:

```php
final readonly class CreateNewsArticleCommandHandler implements CommandHandlerInterface
{
    /** @param CreateNewsArticleCommand $command */
    public function handle(Command $command): void
    {
        $newsArticle = new NewsArticle(
            NewsArticleId::generateRandom(),
            $command->userId,
            $command->title,
            $command->content,
            $command->isPublished,
        );
        ...
```

After:

```php
final readonly class CreateNewsArticleCommandHandler implements CommandHandlerInterface
{
    public function __invoke(CreateNewsArticleCommand $command): void
    {
        $newsArticle = new NewsArticle(
            NewsArticleId::generateRandom(),
            $command->userId,
            $command->title,
            $command->content,
            $command->isPublished,
        );
        ...
```

## From 0.10.* to 0.11.0

### Upgrade to at least PHP 8.2

Support for PHP 8.1 was dropped, so you have to upgrade to at least PHP 8.2.

## From 0.9.* to 0.10.0

### Moved route parameter validation to `RouteBuilder` and made it mandatory

Using the `RouteBuilder` is now **mandatory**. The validation has been moved to `addCommandRoute` and `addQueryRoute`. The `RouteParameters` have been removed in favor of parameters directly for the functions. 

When not using it yet, you have to replace your usages of `RoutePayload::generate` (which has been removed) with the `RouteBuilder` functions.

Before:

```php
$routes->add(
    'api_news_create_news_article_command',
    '/api/news/create-news-article-command',
)
    ->controller([CommandController::class, 'handle'])
    ->methods([Request::METHOD_POST])
    ->defaults([
        'routePayload' => RoutePayload::generate(
            dtoClass: CreateProductNewsArticleCommand::class,
            handlerClass: CreateProductNewsArticleCommandHandler::class,
        ),
    ]);
```

After:

```php
RouteBuilder::addCommandRoute(
    $routes,
    path: '/api/news/create-news-article-command',
    dtoClass: CreateProductNewsArticleCommand::class,
    handlerClass: CreateProductNewsArticleCommandHandler::class,
);
```

## The route name generation changed

The route name generation changed. When the name must be something specific (because it's used as a reference), it must be set as a parameter for `addCommandRoute` and `addQueryRoute`. The name generation might change in future versions. If the name isn't used anywhere, you nether need to nor should set it.

**Only if a name of a route is used as a reference**, add it as a parameter to `addCommandRoute` and `addQueryRoute`.

Before:

```php
RouteBuilder::addCommandRoute(
    $routes,
    path: '/api/news/create-news-article-command',
    dtoClass: CreateProductNewsArticleCommand::class,
    handlerClass: CreateProductNewsArticleCommandHandler::class,
);
```

After:

```php
RouteBuilder::addCommandRoute(
    $routes,
    path: '/api/news/create-news-article-command',
    dtoClass: CreateProductNewsArticleCommand::class,
    handlerClass: CreateProductNewsArticleCommandHandler::class,
    name: 'api_news_create_news_article_command',
);
```

## From 0.8.* to 0.9.0

### Moved files in `DigitalCraftsman\CQRS\ValueObject` to `DigitalCraftsman\CQRS\Routing`

The class `RoutePayload` and the exceptions have been moved to `DigitalCraftsman\CQRS\Routing`. Adapt your imports accordingly. You might replace the usages of `RoutePayload` entirely through using the new `RouteBuilder` ([See routing](./docs/routing.md)).

## From 0.7.* to 0.8.0

### Renamed `Configuration` to `RoutePayload` and converted to a value object

The DTO `Configuration` was renamed to `RoutePayload` and moved from `DigitalCraftsman\CQRS\DTO` to `DigitalCraftsman\CQRS\ValueObject`. The named constructor was also renamed from `routePayload` to `generate`.

The method `generate` now validates the input (through the constructor) and doesn't just rely on Psalm for the validation. The validation is done on warmup of the cache for all routes and for the specific route when triggered. The bundle configuration is validated now as well.

Before:

```php
use DigitalCraftsman\CQSRouting\DTO\Configuration;

'routePayload' => Configuration::routePayload(
    ...
),
```

After:

```php
use DigitalCraftsman\CQSRouting\Routing\RoutePayload;

'routePayload' => RoutePayload::generatePayload(
    ...
),
```

### New method for `RequestValidatorInterface`, `RequestDataTransformerInterface`, `DTOValidatorInterface` and `HandlerWrapperInterface`

The interfaces have been extended with `areParametersValid(mixed $parameters): bool` which validates the parameters of the configuration on cache warmup. All request validators, request data transformers, DTO validators and handler wrappers therefore need to implement this new method.

For example the `SilentExceptionWrapper` validates whether the parameters are an array of exceptions.

```php
/** @param array<array-key, class-string<\Throwable>> $parameters */
public static function areParametersValid(mixed $parameters): bool
{
    if (!is_array($parameters)) {
        return false;
    }

    foreach ($parameters as $exceptionClass) {
        if (!class_exists($exceptionClass)) {
            return false;
        }

        $reflectionClass = new \ReflectionClass($exceptionClass);
        if (!$reflectionClass->implementsInterface(\Throwable::class)) {
            return false;
        }
    }

    return true;
}
```

When there are no parameters needed, the validation can look as simple as this:

```php
/** @param null $parameters */
public static function areParametersValid(mixed $parameters): bool
{
    return $parameters === null;
}
```

### Update handler wrapper configuration

The `HandlerWrapperConfiguration` object was dropped in favor of using the class name as key and supplying the parameters directly as value.

Before:

```php
'routePayload' => Configuration::routePayload(
    handlerWrapperConfigurations: [
        new HandlerWrapperConfiguration(ConnectionTransactionWrapper::class),
        new HandlerWrapperConfiguration(
            handlerWrapperClass: SilentExceptionWrapper::class,
            parameters: [
                EmailAddressDidNotChange::class,
            ],
        ),
    ],
),
```

After:

```php
'routePayload' => Configuration::routePayload(
    handlerWrapperClasses: [
        ConnectionTransactionWrapper::class => null,
        SilentExceptionWrapper::class => [
              EmailAddressDidNotChange::class,
        ],
    ],
),
```

The bundle configuration also needs to be updated to set the classes as key in the configuration of the default handler wrappers and use parameters as value. Use `null` when no parameter is needed. This change enabled the default handler wrappers to use parameters.

Before:

```php
return static function (CqrsConfig $cqrsConfig) {
    $cqrsConfig->queryController()
        ->defaultHandlerWrapperClasses([
            ConnectionTransactionWrapper::class,
        ]);

    $cqrsConfig->commandController()
        ->defaultHandlerWrapperClasses([
            ConnectionTransactionWrapper::class,
        ]);
```

After:

```php
return static function (CqrsConfig $cqrsConfig) {
    $cqrsConfig->queryController()
        ->defaultHandlerWrapperClasses([
            ConnectionTransactionWrapper::class => null,
        ]);

    $cqrsConfig->commandController()
        ->defaultHandlerWrapperClasses([
            ConnectionTransactionWrapper::class => null,
        ]);
```

### Removed `serializer_context` configuration

It was identical with the one that can be defined in the Symfony framework configuration.

Remove it from the CQRS configuration and move your context into the `framework.yaml`.

```yaml
framework:
  serializer:
    default_context:
      # Your context, for example:
      skip_null_values: true
      preserve_empty_objects: true
```

## From 0.6.* to 0.7.0

### Upgrade to at least PHP 8.1

Support for PHP 8.0 was dropped, so you have to upgrade to at least PHP 8.1.

## From 0.5.* to 0.6.0

### Interface `DTODataTransformerInterface` was renamed to `RequestDataTransformerInterface`

The method was renamed from `transformDTOData` to `transformRequestData` and the parameter `$dtoData` was renamed to `$requestData`.

Before:

```php
final class YourCustomDTODataTransformer implements DTODataTransformerInterface
{
    /** @param class-string $dtoClass */
    public function transformDTOData(string $dtoClass, array $dtoData): object
    {
        ...
    }
}
```

After:

```php
final class YourCustomRequestDataTransformer implements RequestDataTransformerInterface
{
    /** @param class-string $dtoClass */
    public function transformRequestData(string $dtoClass, array $requestData): object
    {
        ...
    }
}
```

### Request data parameter in `DTOConstructorInterface`

The parameter `$dtoData` was renamed to `$requestData`.

Before:

```php
final class YourCustomDTOConstructor implements DTOConstructorInterface
{
    /**
     * @return Command|Query
     * 
     * @psalm-template T of Command|Query
     * @psalm-param class-string<T> $dtoClass
     * @psalm-return T
     */
    public function constructDTO(array $dtoData, string $dtoClass): object
    {
        ...
    }
}
```

After:

```php
final class YourCustomDTOConstructor implements DTOConstructorInterface
{
    /**
     * @psalm-template T of Command|Query
     * @psalm-param class-string<T> $dtoClass
     * @psalm-return T
     */
    public function constructDTO(array $requestData, string $dtoClass): Command|Query
    {
        ...
    }
}
```

## From 0.4.* to 0.5.0

### More specific return type for `DTOConstructorInterface`

The `DTOConstructorInterface` now returns `Command|Query` instead of `object`. You need to adapt the return types in your implementations.

Before:

```php
final class YourCustomDTOConstructor implements DTOConstructorInterface
{
    /**
     * @return Command|Query
     * 
     * @psalm-template T of Command|Query
     * @psalm-param class-string<T> $dtoClass
     * @psalm-return T
     */
    public function constructDTO(array $dtoData, string $dtoClass): object
    {
        ...
    }
}
```

After:

```php
final class YourCustomDTOConstructor implements DTOConstructorInterface
{
    /**
     * @psalm-template T of Command|Query
     * @psalm-param class-string<T> $dtoClass
     * @psalm-return T
     */
    public function constructDTO(array $dtoData, string $dtoClass): Command|Query
    {
        ...
    }
}
```

### Removed finally logic in `HandlerWrapperInterface`

The `HandlerWrapperInterface` lost "finally logic". It turns out that there are no cases that can't be handled with just `then` and `catch` and on the other hand, there might be issues when multiple handler wrappers are used and can't be matched with the priority, because `finally` was always triggered last. The methods `finally` and `finallyPriority` have been removed from the interface. The logic of implementations must be adapted in a way that the logic is moved from `finally` into `then` and `catch`.

Before:

```php
final class YourCustomHandlerWrapper implements HandlerWrapperInterface
{
    private ?LockInterface $lock = null;

    public function __construct(
        private LockService $lockService,
    ) {
    }

    /**
     * Only one request per user is handled at once.
     *
     * @param YourActionCommand $dto
     */
    public function prepare(
        Command|Query $dto,
        Request $request,
        mixed $parameters,
    ): void {
        $lockPath = sprintf(
            'your-action-%s',
            (string) $dto->userId,
        );

        $this->lock = $this->lockService->createLock($lockPath);
        $this->lock->acquire(true);
    }

    /** @param null $parameters */
    public function catch(
        Command|Query $dto,
        Request $request,
        mixed $parameters,
        \Exception $exception,
    ): ?\Exception {
        // Nothing to do

        return $exception;
    }

    /** @param null $parameters */
    public function then(
        Command|Query $dto,
        Request $request,
        mixed $parameters,
    ): void {
        // Nothing to do
    }
    
    /** @param null $parameters */
    public function finally(
        Command|Query $dto,
        Request $request,
        mixed $parameters,
    ): void {
        if ($this->lock !== null) {
            $this->lock->release();
        }
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
        return 0;
    }
}
```

After:

```php
final class YourCustomHandlerWrapper implements HandlerWrapperInterface
{
    private ?LockInterface $lock = null;

    public function __construct(
        private LockService $lockService,
    ) {
    }

    /**
     * Only one request per user is handled at once.
     *
     * @param YourActionCommand $dto
     */
    public function prepare(
        Command|Query $dto,
        Request $request,
        mixed $parameters,
    ): void {
        $lockPath = sprintf(
            'your-action-%s',
            (string) $dto->userId,
        );

        $this->lock = $this->lockService->createLock($lockPath);
        $this->lock->acquire(true);
    }

    /** @param null $parameters */
    public function catch(
        Command|Query $dto,
        Request $request,
        mixed $parameters,
        \Exception $exception,
    ): ?\Exception {
        if ($this->lock !== null) {
            $this->lock->release();
        }

        return $exception;
    }

    /** @param null $parameters */
    public function then(
        Command|Query $dto,
        Request $request,
        mixed $parameters,
    ): void {
        if ($this->lock !== null) {
            $this->lock->release();
        }
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
}
```

## From 0.3.* to 0.4.0

### Removed `NullableAsOptionalPropertiesDTODataTransformer`

It turned out that the `NullableAsOptionalPropertiesDTODataTransformer` isn't of any use, as the Symfony serializer in the supported range, already set's nullable properties to `null` when they aren't supplied as data. Therefore, you just have to remove it from all routes you added it to. 

Before:

```php
$routes->add(
    'api_your_domain_your_command',
    '/api/your-domain/your-command',
)
    ->controller([CommandController::class, 'handle'])
    ->methods([Request::METHOD_POST])
    ->defaults([
        'routePayload' => Configuration::routePayload(
            dtoClass: YourCommand::class,
            handlerClass: YourCommandHandler::class,
            dtoDataTransformerClasses: [
                NullableAsOptionalPropertiesDTODataTransformer::class,
            ],
        ),
    ]);
```

After:

```php
$routes->add(
    'api_your_domain_your_command',
    '/api/your-domain/your-command',
)
    ->controller([CommandController::class, 'handle'])
    ->methods([Request::METHOD_POST])
    ->defaults([
        'routePayload' => Configuration::routePayload(
            dtoClass: YourCommand::class,
            handlerClass: YourCommandHandler::class,
        ),
    ]);
```

## From 0.2.* to 0.3.0

### New parameter `string $dtoClass` for `DTODataTransformerInterface`

The `transformDTOData` method in the `DTODataTransformerInterface` was extended with a new parameter `string $dtoClass`. Add this new parameter in your implementations of the interface.

Before:

```php
final class YourCustomDTODataTransformer implements DTODataTransformerInterface
{
    public function transformDTOData(array $dtoData): array
    {
        ...
    }
}
```

After:

```php
final class YourCustomDTODataTransformer implements DTODataTransformerInterface
{
    public function transformDTOData(string $dtoClass, array $dtoData): array
    {
        ...
    }
}
```
