# Changelog

## 0.6.0

- **Breaking change:** The interface `DTODataTransformerInterface` was renamed to `RequestDataTransformerInterface`. The method in it was renamed from `transformDTOData` to `transformRequestData` and the parameter from `$dtoData` to `$requestData`.
- **Breaking change:** The parameter `$dtoData` of `DTOConstructorInterface` was renamed to `$requestData`.

## 0.5.0

- **Breaking change:** `DTOConstructorInterface` now returns `Command|Query` instead of `object`.
- **Breaking change:** `HandlerWrapperInterface` lost "finally logic". It turns out that there are no cases that can't be handled with just `then` and `catch` and on the other hand, there might be issues when multiple handler wrappers are used and can't be matched with the priority, because `finally` was always triggered last. The methods `finally` and `finallyPriority` have been removed from the interface. The logic of implementations must be adapted in a way that the logic is moved from `finally` into `then` and `catch`.
- Increased test code coverage of the moving parts to 100%.
- Marked internal parts as `@internal`.

## 0.4.0

- **Breaking change:** The DTO data transformer that was added in `0.3.0` was removed again as it became clear, that the Symfony Serializer in the full supported range is already able to handle the issue, that DTO Transformer was built for.
- Supported range was increased to allow for Symfony `^6.0`.

## 0.3.0

- **Breaking change:** The `DTODataTransformerInterface` got a new parameter `string $dtoClass` which needs to be added to all implementations of the interface.
- Added new `DTODataTransformer` with which you can automatically handle cases where your command / query is nullable but the Javascript / Typescript client sends `undefined` (which means doesn't send the properties if they are `null`) through reflection.

## 0.2.0

- Validated that the package also works with PHP 8.1 and adapted the allowed range in composer.

## 0.1.0

- Initial release
