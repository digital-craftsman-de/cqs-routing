<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test\Domain\Tasks\WriteSide\CreateTask;

use DigitalCraftsman\CQSRouting\DTOConstructor\DTOConstructor;

final class CreateTaskDTOConstructor implements DTOConstructor
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
    #[\Override]
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
