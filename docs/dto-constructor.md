# DTO constructor

The DTO constructor is there to create the command or query from the array structure. The interface looks like following:

```php
interface DTOConstructorInterface
{
    /**
    * @return Command|Query
    *
    * @psalm-template T of Command|Query
    * @psalm-param class-string<T> $dtoClass
    * @psalm-return T
    */
    public function constructDTO(array $dtoData, string $dtoClass): object;
}
```

## Construction through serializer

A possible implementation of a constructor is one that uses the Symfony serializer like this that is already built-in:

```php
final class SerializerJsonResponseConstructor implements ResponseConstructorInterface
{
    public function __construct(
        private SerializerInterface $serializer,
    ) {
    }

    public function constructResponse($data, Request $request): JsonResponse
    {
        $content = $this->serializer->serialize($data, JsonEncoder::FORMAT, [
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
            AbstractObjectNormalizer::PRESERVE_EMPTY_OBJECTS => true,
        ]);

        return new JsonResponse($content, Response::HTTP_OK, [], true);
    }
}
```

It transforms an array like the following:

```php
[
    'userId' => '5118d20f-0ca5-40d1-99a8-1d1c20d675d6',
    'emailAddress' => 'user@example.com',
    'name' => [
        'firstName' => 'John',
        'lastName' => 'Doe'
    ],
    'password' => 'V6nP2mKmxn42Km3JG3x@'
]
```

Into a DTO like this:

```php
final class CreateUserAccountCommand implements Command
{
    public function __construct(
        public UserId $userId,
        public EmailAddress $emailAddress,
        public Name $name,
        public PlainTextPassword $password,
    ) {
    }
}
```

Obviously it needs the custom denormalizers for the values objects to be able to do so.
