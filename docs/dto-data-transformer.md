# DTO data transformer

The data transformer can have three kinds of tasks and multiple data transformers can be used with one request.

- Cast existing data into other formats.
- Sanitize existing data.
- Add additional data not present in the request.

The interface is the following:

```php
interface DTODataTransformerInterface
{
    public function transformDTOData(array $dtoData): array;
}
```

**Cast existing data into other formats**

Imagine you have a client form with a number field for a discount. It's possible to add percentages in there. Like `2.6` or `7.5`. Now someone enters a full number like `4`. What is this in JSON? It's an `int`. And what will it be in PHP? Also an `int`. So how do you type the DTO? As `int` or as `float`? No matter what you chose, it will fail in one or the other case. That's where you need a data transformer. With it, you could do a specific type casting to `float`.

```php
final class UpdateDiscountDTODataTransformer implements DTODataTransformerInterface
{
    public function transformDTOData(array $dtoData): array
    {
        $dtoData['discount'] = (float) $dtoData['discount'];

        return $dtoData;
    }
}
```

**Sanitize existing data**

We can't always trust the data a user sends. This is especially true when it's not safe content like HTML which might be send through a WYSIWYG editor. We can use a data transformer to sanitize that content.

```php
final class UpdateDescriptionDTODataTransformer implements DTODataTransformerInterface
{
    public function __construct(
        public SanitizationService $sanitizer,
    ) {
    }

    public function transformDTOData(array $dtoData): array
    {
        $dtoData['description'] = $this->sanitizer->sanitizeHTML($dtoData['description']);

        return $dtoData;
    }
}
```

**Add additional data not present in the request**

Sometimes there is data which the user can not or must not have but should be part of the DTO. For those cases we can also use data transformers and add additional data.

```php
final class AddUserManagementRootIdDataTransformer implements DTODataTransformerInterface
{
    public function transformDTOData(array $dtoData): array
    {
        $dtoData['rootId'] = UserManagement::UNIQUE_ROOT_ID;

        return $dtoData;
    }
}
```

## Built-in to handle Javascript / Typescript clients with undefined

Javascript / Typescript clients usually don't use `null` but `undefined`. Undefined is a concept that doesn't really exist in PHP. There is optional which is what you would need to define properties in a command / query when the client doesn't send the necessary properties.

So a command might look like this:

```php
final class CreateUserCommand implements Command
{
    public function __construct(
        public string $id,
        public string $emailAddress,
        public ?Name $name,
        public ?string $registrationReference,
    ) {
    }
}
```

To be able to work with `undefined`, the command would need these changes to work with the Symfony serializer:

```php
public ?Name $name = null,
public ?string $registrationReference = null,
```

But this is of course not what we usually want and in future PHP versions those properties must be at the end no matter the significance you would put into the order of those properties.

It's possible to create a DTO data transformer for every command that contains nullable values and set them to `null` when they aren't part of the `$dtoData`.

But there's also a built-in DTO data transformer `NullableAsOptionalPropertiesDTODataTransformer` that does this automatically through reflection. You can see it in action in the [test for it](/tests/DTODataTransformer/NullableAsOptionalPropertiesDTODataTransformerTest.php).
