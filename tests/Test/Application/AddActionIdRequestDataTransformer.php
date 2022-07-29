<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Application;

use DigitalCraftsman\CQRS\RequestDataTransformer\RequestDataTransformerInterface;
use DigitalCraftsman\CQRS\Test\ValueObject\ActionId;

final class AddActionIdRequestDataTransformer implements RequestDataTransformerInterface
{
    /** @param class-string $dtoClass */
    public function transformRequestData(string $dtoClass, array $requestData): array
    {
        $requestData['actionId'] = (string) ActionId::generateRandom();

        return $requestData;
    }
}
