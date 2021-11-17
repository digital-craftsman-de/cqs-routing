<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\DTOConstructor;

use DigitalCraftsman\CQRS\Command\Command;
use DigitalCraftsman\CQRS\Query\Query;

interface DTOConstructorInterface
{
    /**
     * @return Command|Query
     *
     * @psalm-template T of Command|Query
     * @psalm-param class-string<T> $dtoClass
     * @psalm-return T
     */
    public function constructDTO(array $dtoData, string $dtoClass): object;
}
