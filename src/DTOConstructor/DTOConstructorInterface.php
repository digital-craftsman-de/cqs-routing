<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\DTOConstructor;

use DigitalCraftsman\CQRS\Command\Command;
use DigitalCraftsman\CQRS\Query\Query;

interface DTOConstructorInterface
{
    /**
     * @psalm-template T of Command|Query
     * @psalm-param class-string<T> $dtoClass
     * @psalm-return T
     */
    public function constructDTO(array $requestData, string $dtoClass): Command|Query;
}
