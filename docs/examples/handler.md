# Handler examples

**Interfaces**

The interfaces are simple marker interfaces.

```php
/** @method void __invoke(Command $command) */
interface CommandHandler
{
}
```

```php
/** @method void __invoke(Query $query) */
interface QueryHandler
{
}
```

See [position in process](../process.md#handler)

## Command handler

A command handler implementation to create a new user account might look like this:

```php
final class CreateUserAccountCommandHandler implements CommandHandler
{
    public function __construct(
        private readonly PasswordHasherFactoryInterface $passwordHasherFactory,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function __invoke(CreateUserAccountCommand $command): void
    {
      $this->requestingUserMustBeAdmin($command);

      $this->noUserWithTheSameEmailAddressMustExist($command);

      $this->createNewUser($command);

      $this->sendUserWasCreatedInAppNotificationsToAllAdminUsersExceptRequestingUser($command);

      $this->sendUserWasCreatedEmailNotificationsToAllAdminUsersExceptRequestingUser($command);
    }

    ...

}
```

## Query handler

The query handler always returns a value (if there is no exception). This return value can be anything from an `object`, `array` or even a `callable`. When it returns data, it must not return entities, but always custom read models instead. This is an example where the query handler would return a user read model.

```php
final readonly class GetUserQueryHandler implements QueryHandler
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    public function __invoke(GetUserQuery $query): ReadModel\User
    {
        $this->requestingUserMustBeAdmin($query);

        return $this->getTargetUser($query);
    }

    ...

    private function getTargetUser(GetUserQuery $query): ReadModel\User
    {
        $targetUser = $this->userRepository->findOneById($query->targetUserId);
        if ($targetUser === null) {
            throw new TargetUserNotFound();
        }

        return new ReadModel\User(
            $targetUser->userId,
            $targetUser->name,
            $targetUser->emailAddress,
        );
    }
}
```

Whatever is returned is not send to the client but rather transformed to a response object through the configured response constructor.
