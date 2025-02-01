<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Controller;

use DigitalCraftsman\CQSRouting\DTOConstructor\SerializerDTOConstructor;
use DigitalCraftsman\CQSRouting\HandlerWrapper\SilentExceptionWrapper;
use DigitalCraftsman\CQSRouting\RequestDecoder\JsonRequestDecoder;
use DigitalCraftsman\CQSRouting\ResponseConstructor\EmptyJsonResponseConstructor;
use DigitalCraftsman\CQSRouting\Routing\RoutePayload;
use DigitalCraftsman\CQSRouting\Test\Application\ConnectionTransactionWrapper;
use DigitalCraftsman\CQSRouting\Test\Application\UserIdValidator;
use DigitalCraftsman\CQSRouting\Test\Domain\News\WriteSide\CreateNewsArticle\CreateNewsArticleCommand;
use DigitalCraftsman\CQSRouting\Test\Domain\News\WriteSide\CreateNewsArticle\CreateNewsArticleCommandHandler;
use DigitalCraftsman\CQSRouting\Test\Domain\News\WriteSide\CreateNewsArticle\CreateNewsArticleHandlerWrapper;
use DigitalCraftsman\CQSRouting\Test\Domain\News\WriteSide\CreateNewsArticle\CreateNewsArticleRequestDataTransformer;
use DigitalCraftsman\CQSRouting\Test\Domain\News\WriteSide\CreateNewsArticle\Exception\NewsArticleAlreadyExists;
use DigitalCraftsman\CQSRouting\Test\Domain\News\WriteSide\CreateNewsArticle\FailingCreateNewsArticleCommandHandler;
use DigitalCraftsman\CQSRouting\Test\Helper\ServiceMapHelper;
use DigitalCraftsman\CQSRouting\Test\Repository\NewsArticleInMemoryRepository;
use DigitalCraftsman\CQSRouting\Test\RequestValidator\GuardAgainstFileWithVirusRequestValidator;
use DigitalCraftsman\CQSRouting\Test\Utility\ConnectionSimulator;
use DigitalCraftsman\CQSRouting\Test\Utility\LockSimulator;
use DigitalCraftsman\CQSRouting\Test\Utility\SecuritySimulator;
use DigitalCraftsman\CQSRouting\Test\Utility\VirusScannerSimulator;
use DigitalCraftsman\CQSRouting\Test\ValueObject\UserId;
use DigitalCraftsman\SelfAwareNormalizers\Serializer\ArrayNormalizableNormalizer;
use DigitalCraftsman\SelfAwareNormalizers\Serializer\StringNormalizableNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

/** @coversDefaultClass \DigitalCraftsman\CQSRouting\Controller\CommandController */
final class CommandControllerTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::handle
     */
    public function command_controller_works_with_all_components(): void
    {
        // -- Arrange

        $authenticatedUserId = UserId::generateRandom();
        $serializer = new Serializer([
            new ArrayDenormalizer(),
            new StringNormalizableNormalizer(),
            new ArrayNormalizableNormalizer(),
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
        $securitySimulator = new SecuritySimulator();
        $securitySimulator->fixateAuthenticatedUserId($authenticatedUserId);

        $controller = new CommandController(
            ServiceMapHelper::serviceMap(
                requestValidators: [
                    new GuardAgainstFileWithVirusRequestValidator(new VirusScannerSimulator()),
                ],
                requestDecoders: [
                    new JsonRequestDecoder(),
                ],
                requestDataTransformers: [
                    new CreateNewsArticleRequestDataTransformer(),
                ],
                dtoConstructors: [
                    new SerializerDTOConstructor($serializer),
                ],
                dtoValidators: [
                    new UserIdValidator($securitySimulator),
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
            [],
            JsonRequestDecoder::class,
            [],
            SerializerDTOConstructor::class,
            [],
            [],
            EmptyJsonResponseConstructor::class,
        );

        $content = [
            'userId' => (string) $authenticatedUserId,
            'title' => 'New feature released',
            'content' => '<p>We just released <strong>a new feature</strong> <em>but this em is not allowed</em></p>',
            'isPublished' => false,
        ];

        $request = new Request(content: json_encode($content, JSON_THROW_ON_ERROR));
        $routePayload = RoutePayload::generatePayload(
            dtoClass: CreateNewsArticleCommand::class,
            handlerClass: CreateNewsArticleCommandHandler::class,
            requestValidatorClasses: [
                GuardAgainstFileWithVirusRequestValidator::class => null,
            ],
            requestDataTransformerClasses: [
                CreateNewsArticleRequestDataTransformer::class => null,
            ],
            dtoValidatorClasses: [
                UserIdValidator::class => null,
            ],
            handlerWrapperClasses: [
                CreateNewsArticleHandlerWrapper::class => null,
            ],
        );

        // -- Act
        $controller->handle($request, $routePayload);

        // -- Assert
        self::assertCount(1, $newsArticleInMemoryRepository->newsArticles);
        self::assertCount(1, $lockSimulator->lockedActions);
        self::assertCount(1, $lockSimulator->unlockedActions);
    }

    /**
     * @test
     *
     * @covers ::handle
     */
    public function command_controller_works_with_handler_wrapper_in_catch_case_with_multiple_catches(): void
    {
        // -- Arrange

        $serializer = new Serializer([
            new ArrayDenormalizer(),
            new StringNormalizableNormalizer(),
            new ArrayNormalizableNormalizer(),
            new PropertyNormalizer(
                null,
                null,
                new PropertyInfoExtractor([], [new PhpDocExtractor(), new ReflectionExtractor()]),
            ),
        ], [
            new JsonEncoder(),
        ]);
        $connectionSimulator = new ConnectionSimulator();

        $controller = new CommandController(
            ServiceMapHelper::serviceMap(
                requestDecoders: [
                    new JsonRequestDecoder(),
                ],
                dtoConstructors: [
                    new SerializerDTOConstructor($serializer),
                ],
                handlerWrappers: [
                    new SilentExceptionWrapper(),
                    new ConnectionTransactionWrapper($connectionSimulator),
                ],
                commandHandlers: [
                    new FailingCreateNewsArticleCommandHandler(),
                ],
                responseConstructors: [
                    new EmptyJsonResponseConstructor(),
                ],
            ),
            [],
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
        $routePayload = RoutePayload::generatePayload(
            dtoClass: CreateNewsArticleCommand::class,
            handlerClass: FailingCreateNewsArticleCommandHandler::class,
            handlerWrapperClasses: [
                SilentExceptionWrapper::class => [
                    NewsArticleAlreadyExists::class,
                ],
                ConnectionTransactionWrapper::class => null,
            ],
        );

        // -- Act
        $response = $controller->handle($request, $routePayload);

        // -- Assert
        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        self::assertFalse($connectionSimulator->hasActiveTransaction);
        self::assertFalse($connectionSimulator->hasCommitted);
    }

    /**
     * @test
     *
     * @covers ::handle
     */
    public function command_controller_works_with_handler_wrapper_with_catch_and_throw(): void
    {
        // -- Assert
        $this->expectException(NewsArticleAlreadyExists::class);

        // -- Arrange

        $serializer = new Serializer([
            new ArrayDenormalizer(),
            new StringNormalizableNormalizer(),
            new ArrayNormalizableNormalizer(),
            new PropertyNormalizer(
                null,
                null,
                new PropertyInfoExtractor([], [new PhpDocExtractor(), new ReflectionExtractor()]),
            ),
        ], [
            new JsonEncoder(),
        ]);
        $connectionSimulator = new ConnectionSimulator();

        $controller = new CommandController(
            ServiceMapHelper::serviceMap(
                requestDecoders: [
                    new JsonRequestDecoder(),
                ],
                dtoConstructors: [
                    new SerializerDTOConstructor($serializer),
                ],
                handlerWrappers: [
                    new ConnectionTransactionWrapper($connectionSimulator),
                ],
                commandHandlers: [
                    new FailingCreateNewsArticleCommandHandler(),
                ],
                responseConstructors: [
                    new EmptyJsonResponseConstructor(),
                ],
            ),
            [],
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
        $routePayload = RoutePayload::generatePayload(
            dtoClass: CreateNewsArticleCommand::class,
            handlerClass: FailingCreateNewsArticleCommandHandler::class,
            handlerWrapperClasses: [
                ConnectionTransactionWrapper::class => null,
            ],
        );

        // -- Act
        $controller->handle($request, $routePayload);
    }
}
