<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\DTODataTransformer;

use DigitalCraftsman\CQRS\DTODataTransformer\Exception\UntypedProperty;

/**
 * A data transformer that adds null as value for all properties of the DTO class that are nullable and aren't provided.
 * It can be used, when working with a typical Javascript or Typescript based client that doesn't differentiate between undefined and null.
 */
final class NullableAsOptionalPropertiesDTODataTransformer implements DTODataTransformerInterface
{
    /** @param class-string $dtoClass */
    public function transformDTOData(
        string $dtoClass,
        array $dtoData,
    ): array {
        $reflection = new \ReflectionClass($dtoClass);
        foreach ($reflection->getProperties() as $property) {
            $propertyType = $property->getType();
            if ($propertyType === null) {
                throw new UntypedProperty();
            }

            $propertyName = $property->getName();

            if ($propertyType->allowsNull()
                && !array_key_exists($propertyName, $dtoData)
            ) {
                $dtoData[$propertyName] = null;
            }
        }

        return $dtoData;
    }
}
