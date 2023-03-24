<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Routing;

use PHPUnit\Framework\TestCase;

/** @coversDefaultClass \DigitalCraftsman\CQRS\Routing\RouteBuilder */
class RouteBuilderTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::generateNameFromPath
     *
     * @dataProvider dataProvider
     */
    public function generate_name_from_path_works(
        string $expectedResult,
        string $name,
    ): void {
        // -- Act & Assert
        self::assertSame($expectedResult, RouteBuilder::generateNameFromPath($name));
    }

    /**
     * @return array<string, array{
     *   0: string,
     *   1: string,
     * }>
     */
    public function dataProvider(): array
    {
        return [
            'route with slash at the beginning' => [
                'api_tasks_create_task_command',
                '/api/tasks/create-task-command',
            ],
            'route without slash at the beginning' => [
                'api_tasks_create_task_command',
                'api/tasks/create-task-command',
            ],
            'route with parameters' => [
                'api_tasks_get_task_image_query_id',
                '/api/tasks/get-task-image-query/{id}',
            ],
        ];
    }
}
