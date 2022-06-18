<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Application;

use DigitalCraftsman\CQRS\DTODataTransformer\DTODataTransformerInterface;
use DigitalCraftsman\CQRS\Test\ValueObject\ActionId;

final class AddActionIdDTODataTransformer implements DTODataTransformerInterface
{
    /** @param class-string $dtoClass */
    public function transformDTOData(string $dtoClass, array $dtoData): array
    {
        $dtoData['actionId'] = (string) ActionId::generateRandom();

        return $dtoData;
    }
}
