# Changelog

## 0.9.0

- **[Breaking change](./UPGRADE.md#moved-files-in-digitalcraftsmancqrsvalueobject-to-digitalcraftsmancqrsrouting)** Moved files in `DigitalCraftsman\CQRS\ValueObject` to `DigitalCraftsman\CQRS\Routing`.
- Added `RouteBuilder` to reduce noise in routing configuration.

## 0.8.1

- Improve debugging of route configuration.
- Added missing return type annotation for `RoutePayload::generate`.

## 0.8.0

- **[Breaking change](./UPGRADE.md#renamed-configuration-to-routepayload-and-converted-to-a-value-object)**: Renamed `Configuration` to `RoutePayload` and converted to a value object
- **[Breaking change](./UPGRADE.md#new-method-for-requestvalidatorinterface-requestdatatransformerinterface-dtovalidatorinterface-and-handlerwrapperinterface)**: Added `areParametersValid` method to `RequestValidatorInterface`, `RequestDataTransformerInterface`, `DTOValidatorInterface` and `HandlerWrapperInterface`
- **[Breaking change](./UPGRADE.md#update-handler-wrapper-configuration)**: Replaced `HandlerWrapperConfiguration` with simple map configuration.
- **[Breaking change](./UPGRADE.md#removed-serializer_context-configuration)**: Removed `serializer_context` configuration. It was identical with the one that can be defined in the Symfony framework configuration.
- **Breaking change**: Added the parameters `requestValidatorClassesToMergeWithDefault`, `requestDataTransformerClassesToMergeWithDefault`, `dtoValidatorClassesToMergeWithDefault` or `handlerWrapperClassesToMergeWithDefault` to `RoutePayload`. This change is only breaking when you don't use named parameters in your routing (which is highly recommended). Using those parameters instead the version without `*ToMergeWithDefault` merges the configuration with the default instead of replacing it ([see routing docs](./docs/routing.md#merge-configuration-from-request-validators-request-data-transformers-dto-validators-and-handler-wrappers-with-default)).
- Enabled usage of parameters in route configuration for request validators, request data transformers and DTO validators.
- Enabled usage of parameters in default configuration for request validators, request data transformers, DTO validators and handler wrappers.

## 0.7.0

- **[Breaking change](./UPGRADE.md#upgrade-to-at-least-php-81)**: Drop support for PHP 8.0.
- Add support for PHP 8.2.

## 0.6.0

- **[Breaking change](./UPGRADE.md#interface-dtodatatransformerinterface-was-renamed-to-requestdatatransformerinterface):** The interface `DTODataTransformerInterface` was renamed to `RequestDataTransformerInterface`. The method in it was renamed from `transformDTOData` to `transformRequestData` and the parameter from `$dtoData` to `$requestData`.
- **[Breaking change](./UPGRADE.md#request-data-parameter-in-dtoconstructorinterface):** The parameter `$dtoData` of `DTOConstructorInterface` was renamed to `$requestData`.
- New component `RequestValidatorInterface` to validate information that is only accessible from the request itself and will not be part of the DTO or must be validated before a DTO is constructed from the request data.
- Added process description as part of the documentation including graph.

## 0.5.0

- **[Breaking change](./UPGRADE.md#more-specific-return-type-for-dtoconstructorinterface):** `DTOConstructorInterface` now returns `Command|Query` instead of `object`.
- **[Breaking change](./UPGRADE.md#removed-finally-logic-in-handlerwrapperinterface):** `HandlerWrapperInterface` lost "finally logic". It turns out that there are no cases that can't be handled with just `then` and `catch` and on the other hand, there might be issues when multiple handler wrappers are used and can't be matched with the priority, because `finally` was always triggered last. The methods `finally` and `finallyPriority` have been removed from the interface. The logic of implementations must be adapted in a way that the logic is moved from `finally` into `then` and `catch`.
- Increased test code coverage of the moving parts to 100%.
- Marked internal parts as `@internal`.

## 0.4.0

- **[Breaking change](./UPGRADE.md#removed-nullableasoptionalpropertiesdtodatatransformer):** The DTO data transformer that was added in `0.3.0` was removed again as it became clear, that the Symfony Serializer in the full supported range is already able to handle the issue, that DTO Transformer was built for.
- Supported range was increased to allow for Symfony `^6.0`.

## 0.3.0

- **[Breaking change](./UPGRADE.md#new-parameter-string-dtoclass-for-dtodatatransformerinterface):** The `DTODataTransformerInterface` got a new parameter `string $dtoClass` which needs to be added to all implementations of the interface.
- Added new `DTODataTransformer` with which you can automatically handle cases where your command / query is nullable but the Javascript / Typescript client sends `undefined` (which means doesn't send the properties if they are `null`) through reflection.

## 0.2.0

- Validated that the package also works with PHP 8.1 and adapted the allowed range in composer.

## 0.1.0

- Initial release
