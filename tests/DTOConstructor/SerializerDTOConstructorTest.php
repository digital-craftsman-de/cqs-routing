<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\DTOConstructor;

use DigitalCraftsman\CQRS\Test\Domain\News\WriteSide\CreateNewsArticle\CreateNewsArticleCommand;
use DigitalCraftsman\CQRS\Test\ValueObject\UserId;
use DigitalCraftsman\Ids\Serializer\IdNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

/** @coversDefaultClass \DigitalCraftsman\CQRS\DTOConstructor\SerializerDTOConstructor */
final class SerializerDTOConstructorTest extends TestCase
{
    /**
     * @test
     * @covers ::constructDTO
     */
    public function serializer_dto_constructor_constructs_dto(): void
    {
        // -- Arrange
        $serializerDTOConstructor = new SerializerDTOConstructor(
            new Serializer([
                new IdNormalizer(),
                new PropertyNormalizer(),
            ], [
                new JsonEncoder(),
            ]),
        );

        $requestData = [
            'userId' => (string) UserId::generateRandom(),
            'title' => 'New project',
            'content' => 'We published a new project.',
            'isPublished' => true,
        ];

        // -- Act
        /** @var CreateNewsArticleCommand $command */
        $command = $serializerDTOConstructor->constructDTO($requestData, CreateNewsArticleCommand::class);

        // -- Assert
        self::assertSame(CreateNewsArticleCommand::class, $command::class);
        self::assertSame($requestData['userId'], (string) $command->userId);
        self::assertSame($requestData['title'], $command->title);
        self::assertSame($requestData['content'], $command->content);
        self::assertSame($requestData['isPublished'], $command->isPublished);
    }
}
