<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Domain\Tasks\WriteSide\DefineTaskHourContingent;

use DigitalCraftsman\CQRS\RequestDataTransformer\RequestDataTransformerInterface;

final class DefineTaskHourContingentRequestDataTransformer implements RequestDataTransformerInterface
{
    /**
     * @param class-string $dtoClass
     * @param array{
     *   hourContingent: int|float,
     * } $requestData
     */
    public function transformRequestData(string $dtoClass, array $requestData): array
    {
        $requestData['hourContingent'] = (float) $requestData['hourContingent'];

        return $requestData;
    }
}
