<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\RequestDataTransformer;

interface RequestDataTransformerInterface
{
    /** @param class-string $dtoClass */
    public function transformRequestData(string $dtoClass, array $requestData): array;
}
