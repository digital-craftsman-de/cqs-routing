# Routing

For better typing and refactoring, the routing must be configured with PHP files instead of the usual YAML files.

When the routes are generated (on cache warmup), they are cached as PHP files. Therefore, the configuration can't contain any object instances. For increased type safety and better DX, we use a route builder with named parameters to configure our routes. All parameters are validated on cache warmup.

```php
return static function (RoutingConfigurator $routes) {

    RouteBuilder::addCommandRoute(
        $routes,
        path: '/api/news/create-news-article-command',
        dtoClass: CreateProductNewsArticleCommand::class,
        handlerClass: CreateProductNewsArticleCommandHandler::class,
        requestDecoderClass: CommandWithFilesRequestDecoder::class,
        dtoValidatorClasses: [
            UserIdValidator::class => null,
        ],
    );
    ...
    
};
```

All parameters except `path`, `dtoClass` and `handlerClass` are optional. You might only need to define those when you only need the default components for the other parameters ([configured in the `cqs-routing.php`](./configuration.md)).

The `RouteBuilder` chooses the controller depending on the function used (`addCommandRoute` or `addQueryRoute`), uses `POST` as default method, generates the name based on the path and validates all parameters. The routes are constructed on cache warmup, so that's the only time the validation costs performance.

## Overwrite request validators, request data transformers, DTO validators and handler wrappers

The request validators, request data transformers, DTO validators and handler wrappers are defined as an array. It's important to know that when you use the `requestValidatorClasses`, `requestDataTransformerClasses`, `dtoValidatorClasses` or `handlerWrapperClasses` parameters, the defaults are overwritten and not extended.

Meaning when your default DTO validators are defined with one validator like this:

```php
return static function (CqsRoutingConfig $cqsRoutingConfig) {
    $cqsRoutingConfig->commandController()
        ->defaultDtoValidatorClasses([
            UserIdValidator::class => null,
        ]);
```

And you then adapt it like this:

```php
RouteBuilder::addCommandRoute(
    ...
    dtoValidatorClasses: [
        FilesizeValidator::class => null,
    ],
));
```

The end result is that only the `FilesizeValidator` is left. You need to include the defaults if you still want them to be there. 

This way you're also able to remove all default DTO validators from a route like this:

```php
RouteBuilder::addCommandRoute(
    ...
    dtoValidatorClasses: [],
));
```

## Merge configuration from request validators, request data transformers, DTO validators and handler wrappers with default

Another option is to merge the route parameters with the defaults. You can use the properties `requestValidatorClassesToMergeWithDefault`, `requestDataTransformerClassesToMergeWithDefault`, `dtoValidatorClassesToMergeWithDefault` and `handlerWrapperClassesToMergeWithDefault` for this.

Meaning when your default DTO validators are defined with one validator like this:

```php
return static function (CqsRoutingConfig $cqsRoutingConfig) {
    $cqsRoutingConfig->commandController()
        ->defaultDtoValidatorClasses([
            UserIdValidator::class => null,
        ]);
```

And you then use the following route configuration:

```php
RouteBuilder::addCommandRoute(
    ...
    dtoValidatorClassesToMergeWithDefault: [
        CourseIdValidator::class => null,
    ],
));
```

The end result is the combination, meaning `UserIdValidator` and `CourseIdValidator`.

### Overwrite default parameters

When the same class is used in the default configuration and in the route configuration, then the parameters of the route configuration has priority and will be used.

With the following default configuration:

```php
return static function (CqsRoutingConfig $cqsRoutingConfig) {
    $cqsRoutingConfig->commandController()
        ->defaultDtoValidatorClasses([
            UserIdValidator::class => null,
            FilesizeValidator::class => 5,
        ]);
```

And the following route configuration:

```php
RouteBuilder::addCommandRoute(
    ...
    dtoValidatorClassesToMergeWithDefault: [
        FilesizeValidator::class => 10,
    ],
));
```

The end result will be `UserIdValidator` with parameter `null` and `FilesizeValidator` with a parameter of `10`.
