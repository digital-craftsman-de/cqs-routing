<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Domain\Tasks\WriteSide\DefineTaskHourContingent;

use DigitalCraftsman\CQRS\DTODataTransformer\DTODataTransformerInterface;

final class DefineTaskHourContingentDTODataTransformer implements DTODataTransformerInterface
{
    /**
     * @param class-string $dtoClass
     * @param array{
     *   content: int|float,
     * } $dtoData
     */
    public function transformDTOData(string $dtoClass, array $dtoData): array
    {
        $dtoData['hourContingent'] = (float) $dtoData['hourContingent'];

        return $dtoData;
    }
}
