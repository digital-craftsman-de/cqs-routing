<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test\Application;

use DigitalCraftsman\CQSRouting\Command\Command;
use DigitalCraftsman\CQSRouting\Query\Query;
use DigitalCraftsman\CQSRouting\RequestDataTransformer\RequestDataTransformer;
use DigitalCraftsman\CQSRouting\Test\ValueObject\ActionId;

final class AddActionIdRequestDataTransformer implements RequestDataTransformer
{
    /**
     * @param class-string<Command|Query> $dtoClass
     * @param null                        $parameters
     */
    public function transformRequestData(
        string $dtoClass,
        array $requestData,
        mixed $parameters,
    ): array {
        $requestData['actionId'] = (string) ActionId::generateRandom();

        return $requestData;
    }

    /** @param null $parameters */
    public static function areParametersValid(mixed $parameters): bool
    {
        return $parameters === null;
    }
}
