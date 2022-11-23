<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Application;

use DigitalCraftsman\CQRS\Command\Command;
use DigitalCraftsman\CQRS\Query\Query;
use DigitalCraftsman\CQRS\RequestDataTransformer\RequestDataTransformerInterface;
use DigitalCraftsman\CQRS\Test\ValueObject\ActionId;

final class AddActionIdRequestDataTransformer implements RequestDataTransformerInterface
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
}
