# Routing

For better typing and refactoring, the routing must be configured with PHP files instead of the usual YAML files.

When the routes are generated (on cache warmup), they are cached as PHP files. Therefore, the configuration can't contain any object instances. For increased type safety and better DX, we use a value object with named parameters to configure our routes. All parameters are validated on cache warmup and the specific route again on execution.

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
            requestDecoderClass: CommandWithFilesRequestDecoder::class,
            dtoValidatorClasses: [
                UserIdValidator::class => null,
            ],
        ),
    ]);
```

All parameters except `dtoClass` and `handlerClass` are optional. You might only need to define those when you only need the default components for the other parameters ([configured in the `cqrs.yaml`](./configuration.md)).

## Overwrite DTO validators or handler wrappers

The DTO validators and handler wrappers are defined as an array. It's imporant to know that when you use the `dtoValidatorClasses` or `handlerWrapperConfigurations` parameter, the defaults are overwritten and not extended.

Meaning when your default DTO validators are defined with one validator like this:

```php
return static function (CqrsConfig $cqrsConfig) {
    $cqrsConfig->commandController()
        ->defaultDtoValidatorClasses([
            UserIdValidator::class => null,
        ]);
```

And you then adapt it like this:

```php
'routePayload' => RoutePayload::generate(
    dtoValidatorClasses: [
        FilesizeValidator::class => null,
    ],
),
```

The end result is that only the `FilesizeValidator` is left. You need to include the defaults if you still want them to be there. 

This way you're also able to remove all default DTO validators from a route like this:

```php
'routePayload' => RoutePayload::generate(
    dtoValidatorClasses: [],
),
```
