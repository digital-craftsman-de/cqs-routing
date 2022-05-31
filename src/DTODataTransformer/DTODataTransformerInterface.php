<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\DTODataTransformer;

interface DTODataTransformerInterface
{
    /** @param class-string $dtoClass */
    public function transformDTOData(string $dtoClass, array $dtoData): array;
}
