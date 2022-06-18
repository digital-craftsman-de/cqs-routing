<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Controller;

use DigitalCraftsman\CQRS\DTO\Configuration;
use DigitalCraftsman\CQRS\DTO\HandlerWrapperConfiguration;
use DigitalCraftsman\CQRS\DTOConstructor\SerializerDTOConstructor;
use DigitalCraftsman\CQRS\RequestDecoder\JsonRequestDecoder;
use DigitalCraftsman\CQRS\ResponseConstructor\EmptyJsonResponseConstructor;
use DigitalCraftsman\CQRS\ServiceMap\ServiceMap;
use DigitalCraftsman\CQRS\Test\Domain\News\WriteSide\CreateNewsArticle\CreateNewsArticleCommand;
use DigitalCraftsman\CQRS\Test\Domain\News\WriteSide\CreateNewsArticle\CreateNewsArticleCommandHandler;
use DigitalCraftsman\CQRS\Test\Domain\News\WriteSide\CreateNewsArticle\CreateNewsArticleDTODataTransformer;
use DigitalCraftsman\CQRS\Test\Domain\News\WriteSide\CreateNewsArticle\CreateNewsArticleHandlerWrapper;
use DigitalCraftsman\CQRS\Test\Lock\LockSimulator;
use DigitalCraftsman\CQRS\Test\Repository\NewsArticleInMemoryRepository;
use DigitalCraftsman\CQRS\Test\ValueObject\UserId;
use DigitalCraftsman\Ids\Serializer\IdNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

/** @coversDefaultClass \DigitalCraftsman\CQRS\Controller\CommandController */
final class CommandControllerTest extends TestCase
{
    /**
     * @test
     * @covers ::handle
     */
    public function handle_works(): void
    {
        // -- Arrange

        $serializer = new Serializer([
            new ArrayDenormalizer(),
            new IdNormalizer(),
            new PropertyNormalizer(
                null,
                null,
                new PropertyInfoExtractor([], [new PhpDocExtractor(), new ReflectionExtractor()]),
            ),
        ], [
            new JsonEncoder(),
        ]);
        $newsArticleInMemoryRepository = new NewsArticleInMemoryRepository();
        $lockSimulator = new LockSimulator();

        $controller = new CommandController(
            new ServiceMap(
                requestDecoders: [
                    new JsonRequestDecoder(),
                ],
                dtoDataTransformers: [
                    new CreateNewsArticleDTODataTransformer(),
                ],
                dtoConstructors: [
                    new SerializerDTOConstructor($serializer),
                ],
                handlerWrappers: [
                    new CreateNewsArticleHandlerWrapper($lockSimulator),
                ],
                commandHandlers: [
                    new CreateNewsArticleCommandHandler($newsArticleInMemoryRepository),
                ],
                responseConstructors: [
                    new EmptyJsonResponseConstructor(),
                ],
            ),
            JsonRequestDecoder::class,
            [],
            SerializerDTOConstructor::class,
            [],
            [],
            EmptyJsonResponseConstructor::class,
        );

        $content = [
            'userId' => (string) UserId::generateRandom(),
            'title' => 'New feature released',
            'content' => '<p>We just released <strong>a new feature</strong> <em>but this em is not allowed</em></p>',
            'isPublished' => false,
        ];

        $request = new Request(content: json_encode($content, JSON_THROW_ON_ERROR));
        $routePayload = Configuration::routePayload(
            dtoClass: CreateNewsArticleCommand::class,
            handlerClass: CreateNewsArticleCommandHandler::class,
            dtoDataTransformerClasses: [
                CreateNewsArticleDTODataTransformer::class,
            ],
            handlerWrapperConfigurations: [
                new HandlerWrapperConfiguration(CreateNewsArticleHandlerWrapper::class),
            ],
        );

        // -- Act
        $controller->handle($request, $routePayload);

        // -- Assert
        self::assertCount(1, $newsArticleInMemoryRepository->newsArticles);
        self::assertCount(1, $lockSimulator->lockedActions);
        self::assertCount(1, $lockSimulator->unlockedActions);
    }
}
