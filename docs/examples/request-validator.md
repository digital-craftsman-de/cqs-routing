# Request validator examples

**Interface**

```php
interface RequestValidator
{
    /** @param scalar|array<array-key, scalar|null>|null $parameters */
    public function validateRequest(
        Request $request,
        mixed $parameters,
    ): void;

    /** @param scalar|array<array-key, scalar|null>|null $parameters */
    public static function areParametersValid(mixed $parameters): bool;
}
```

See [position in process](../process.md#request-validator)

## Scan for viruses in files

You might want to validate files that are uploaded against virus databases before they are given to the business logic. You might have a separate class `VirusFreeFile` that extends `UploadedFile` and is constructed as part of the DTO. So you need to do the virus scan process before you construct the command. Your validator might look like the following:

```php
final readonly class VirusFreeFilesRequestValidator implements RequestValidator
{
    public function __construct(
        private VirusScanner $virusScanner,
    ) {
    }

    /** @param null $parameters */
    public function validateRequest(
        Request $request,
        mixed $parameters,
    ): void {
        foreach ($request->files as $file) {
            // Throws exception if it finds a virus in the file.
            $this->virusScanner->scanFile($file);
        }
    }
    
    /** @param null $parameters */
    public static function areParametersValid(mixed $parameters): bool
    {
        return $parameters === null;
    }
}
```

With such a validator in place, there is no way for a file with a virus to reach the business logic. Using such a validator as one of the default validators means you're safe throughout your application.
