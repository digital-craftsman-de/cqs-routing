<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\DTODataTransformer;

interface DTODataTransformerInterface
{
    public function transformDTOData(array $dtoData): array;
}
