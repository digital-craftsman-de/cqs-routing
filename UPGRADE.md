# Upgrade guide

## From 0.5.* to 0.6.0

**Route configuration uses options**

Previously the route configuration was configured using the `defaults()` method of the route. But this was kind of hack to transport the information to the controller. The defaults aren't really meant to be used for configuration. A more appropriate way is using the `options()` method. And that's what was introduced in 0.6.0 including a few other class and method name changes.

Relevant for updating to the new version: `Configuration` was renamed to `RouteConfiguration` and the method `routePayload` was renamed to `routeOptions`.

Before:

```php
$routes->add(
    'api_blog_create_blog_article_command',
    '/api/blog/create-blog-article-command',
)
    ->controller([CommandController::class, 'handle'])
    ->methods([Request::METHOD_POST])
    ->defaults([
        'routePayload' => Configuration::routePayload(
            dtoClass: CreateBlogArticleCommand::class,
            handlerClass: CreateBlogArticleCommandHandler::class,
        ),
    ]);
```

After:

```php
$routes->add(
    'api_blog_create_blog_article_command',
    '/api/blog/create-blog-article-command',
)
    ->controller([CommandController::class, 'handle'])
    ->methods([Request::METHOD_POST])
    ->options(RouteConfiguration::routeOptions(
        dtoClass: CreateBlogArticleCommand::class,
        handlerClass: CreateBlogArticleCommandHandler::class,
    ));
```

## From 0.4.* to 0.5.0

**Changes to the `DTOConstructorInterface`**

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

**Changes to the `HandlerWrapperInterface`**

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
