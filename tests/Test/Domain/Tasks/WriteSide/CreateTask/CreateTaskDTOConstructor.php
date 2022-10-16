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
     * } $requestData
     *
     * @psalm-suppress ImplementedReturnTypeMismatch
     */
    public function constructDTO(
        array $requestData,
        string $dtoClass,
    ): CreateTaskCommand {
        return new CreateTaskCommand(
            $requestData['title'],
            $requestData['content'],
            $requestData['priority'],
        );
    }
}
