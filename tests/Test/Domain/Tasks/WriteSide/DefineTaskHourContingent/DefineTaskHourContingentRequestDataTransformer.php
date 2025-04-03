<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test\Domain\Tasks\WriteSide\DefineTaskHourContingent;

use DigitalCraftsman\CQSRouting\RequestDataTransformer\RequestDataTransformer;

final class DefineTaskHourContingentRequestDataTransformer implements RequestDataTransformer
{
    /**
     * @param class-string $dtoClass
     * @param array{
     *   hourContingent: int|float,
     * } $requestData
     * @param null $parameters
     */
    #[\Override]
    public function transformRequestData(string $dtoClass, array $requestData, mixed $parameters): array
    {
        $requestData['hourContingent'] = (float) $requestData['hourContingent'];

        return $requestData;
    }

    /** @param null $parameters */
    #[\Override]
    public static function areParametersValid(mixed $parameters): bool
    {
        return $parameters === null;
    }
}
