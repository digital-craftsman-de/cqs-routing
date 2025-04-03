# Request data transformer examples

**Interface**

```php
interface RequestDataTransformer
{
    /**
     * @param class-string<Command|Query>               $dtoClass
     * @param scalar|array<array-key, scalar|null>|null $parameters
     */
    public function transformRequestData(
        string $dtoClass,
        array $requestData,
        mixed $parameters,
    ): array;

    /** @param scalar|array<array-key, scalar|null>|null $parameters */
    public static function areParametersValid(mixed $parameters): bool;
}
```

See [position in process](../process.md#request-data-transformer)

## Cast existing data into other formats

Imagine you have a client form with a number field for a discount. It's possible to add percentages in there. Like `2.6` or `7.5`. Now someone enters a full number like `4`. What is this in JSON? It's an `int`. And what will it be in PHP? Also, an `int`. So how do you type the DTO? As `int` or as `float`? No matter what you chose, it will fail in one or the other case. That's where you need a data transformer. With it, you could do a specific type casting to `float`.

```php
final readonly class UpdateDiscountRequestDataTransformer implements RequestDataTransformer
{
    /**
     * @param class-string<UpdateDiscountCommand> $dtoClass
     * @param null                                $parameters
     */
    public function transformRequestData(
        string $dtoClass, 
        array $requestData,
        mixed $parameters,
    ): array {
        $requestData['discount'] = (float) $requestData['discount'];

        return $requestData;
    }
    
    /** @param null $parameters */
    public static function areParametersValid(mixed $parameters): bool
    {
        return $parameters === null;
    }
}
```

## Sanitize existing data

We can't always trust the data a user sends. This is especially true when it's not safe content like HTML which might be sent through a WYSIWYG editor. We can use a data transformer to sanitize that content.

```php
final readonly class UpdateDescriptionRequestDataTransformer implements RequestDataTransformer
{
    public function __construct(
        public SanitizationService $sanitizer,
    ) {
    }

    /**
     * @param class-string<UpdateDescriptionCommand> $dtoClass
     * @param null                                   $parameters
     */
    public function transformRequestData(
        string $dtoClass, 
        array $requestData,
        mixed $parameters,
    ): array {
        $requestData['description'] = $this->sanitizer->sanitizeHTML($requestData['description']);

        return $requestData;
    }
    
    /** @param null $parameters */
    public static function areParametersValid(mixed $parameters): bool
    {
        return $parameters === null;
    }
}
```

## Add additional data not present in the request

Sometimes there is data which the user can not or must not have but should be part of the DTO. For those cases we can also use data transformers and add additional data.

```php
final readonly class AddUserManagementRootIdRequestDataTransformer implements RequestDataTransformer
{
    /**
     * @param class-string<Command|Query> $dtoClass
     * @param null                        $parameters
     */
    public function transformRequestData(
        string $dtoClass, 
        array $requestData,
        mixed $parameters,
    ): array {
        $requestData['rootId'] = UserManagement::UNIQUE_ROOT_ID;

        return $requestData;
    }
    
    /** @param null $parameters */
    public static function areParametersValid(mixed $parameters): bool
    {
        return $parameters === null;
    }
}
```
