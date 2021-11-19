<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\DTOConstructor;

use DigitalCraftsman\CQRS\DTOConstructor\Command\CreateNewsArticleCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

final class SerializerDTOConstructorTest extends TestCase
{
    /** @test */
    public function serializer_dto_constructor_constructs_dto(): void
    {
        // Arrange
        $serializerDTOConstructor = new SerializerDTOConstructor(
            new Serializer([new PropertyNormalizer()], [new JsonEncoder()]),
        );

        $dtoData = [
            'userId' => 'dbb19314-9ad5-4cce-abb2-6abff22235e3',
            'title' => 'New project',
            'content' => 'We published a new project.',
            'isPublished' => true,
        ];

        // Act
        /** @var CreateNewsArticleCommand $command */
        $command = $serializerDTOConstructor->constructDTO($dtoData, CreateNewsArticleCommand::class);

        // Assert
        self::assertSame(CreateNewsArticleCommand::class, $command::class);
        self::assertSame($dtoData['userId'], $command->userId);
        self::assertSame($dtoData['title'], $command->title);
        self::assertSame($dtoData['content'], $command->content);
        self::assertSame($dtoData['isPublished'], $command->isPublished);
    }
}
