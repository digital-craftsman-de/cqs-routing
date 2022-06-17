<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Application;

use DigitalCraftsman\CQRS\DTODataTransformer\DTODataTransformerInterface;

final class AddActionHashDTODataTransformer implements DTODataTransformerInterface
{
    /** @param class-string $dtoClass */
    public function transformDTOData(string $dtoClass, array $dtoData): array
    {
        $dtoData['actionHash'] = bin2hex(random_bytes(20));

        return $dtoData;
    }
}
