<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Domain\Tasks\WriteSide\CreateTask;

use DigitalCraftsman\CQRS\DTOConstructor\DTOConstructorInterface;

final class CreateTaskDTOConstructor implements DTOConstructorInterface
{
    /**
     * @param array{
     *   title: string,
     *   content: string,
     *   priority: string,
     * } $dtoData
     * @psalm-suppress ImplementedReturnTypeMismatch
     */
    public function constructDTO(
        array $dtoData,
        string $dtoClass,
    ): CreateTaskCommand {
        return new CreateTaskCommand(
            $dtoData['title'],
            $dtoData['content'],
            $dtoData['priority'],
        );
    }
}
