<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Controller;

use DigitalCraftsman\CQSRouting\DTOConstructor\SerializerDTOConstructor;
use DigitalCraftsman\CQSRouting\RequestDecoder\JsonRequestDecoder;
use DigitalCraftsman\CQSRouting\ResponseConstructor\EmptyJsonResponseConstructor;
use DigitalCraftsman\CQSRouting\ResponseConstructor\SerializerJsonResponseConstructor;
use DigitalCraftsman\CQSRouting\Routing\RouteConfigurationBuilder;
use DigitalCraftsman\CQSRouting\Routing\RoutePayload;
use DigitalCraftsman\CQSRouting\Test\Application\AddActionIdRequestDataTransformer;
use DigitalCraftsman\CQSRouting\Test\Application\UserIdValidator;
use DigitalCraftsman\CQSRouting\Test\Domain\Tasks\ReadSide\GetTasks\Exception\TasksNotAccessible;
use DigitalCraftsman\CQSRouting\Test\Domain\Tasks\ReadSide\GetTasks\FailingGetTasksQueryHandler;
use DigitalCraftsman\CQSRouting\Test\Domain\Tasks\ReadSide\GetTasks\GetTasksHandlerWrapper;
use DigitalCraftsman\CQSRouting\Test\Domain\Tasks\ReadSide\GetTasks\GetTasksQuery;
use DigitalCraftsman\CQSRouting\Test\Domain\Tasks\ReadSide\GetTasks\GetTasksQueryHandler;
use DigitalCraftsman\CQSRouting\Test\Entity\Task;
use DigitalCraftsman\CQSRouting\Test\Helper\ServiceMapHelper;
use DigitalCraftsman\CQSRouting\Test\Repository\TasksInMemoryRepository;
use DigitalCraftsman\CQSRouting\Test\RequestValidator\GuardAgainstFileWithVirusRequestValidator;
use DigitalCraftsman\CQSRouting\Test\Utility\LockSimulator;
use DigitalCraftsman\CQSRouting\Test\Utility\SecuritySimulator;
use DigitalCraftsman\CQSRouting\Test\Utility\VirusScannerSimulator;
use DigitalCraftsman\CQSRouting\Test\ValueObject\TaskId;
use DigitalCraftsman\CQSRouting\Test\ValueObject\UserId;
use DigitalCraftsman\SelfAwareNormalizers\Serializer\ArrayNormalizableNormalizer;
use DigitalCraftsman\SelfAwareNormalizers\Serializer\StringNormalizableNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

/** @coversDefaultClass \DigitalCraftsman\CQSRouting\Controller\QueryController */
final class QueryControllerTest extends TestCase
{
    private RouteConfigurationBuilder $defaultRouteConfigurationBuilder;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->defaultRouteConfigurationBuilder = new RouteConfigurationBuilder(
            defaultRequestValidatorClassesForCommand: [],
            defaultRequestDecoderClassForCommand: JsonRequestDecoder::class,
            defaultRequestDataTransformerClassesForCommand: [],
            defaultDTOConstructorClassForCommand: SerializerDTOConstructor::class,
            defaultDTOValidatorClassesForCommand: [],
            defaultHandlerWrapperClassesForCommand: [],
            defaultResponseConstructorClassForCommand: EmptyJsonResponseConstructor::class,
            defaultRequestValidatorClassesForQuery: [],
            defaultRequestDecoderClassForQuery: JsonRequestDecoder::class,
            defaultRequestDataTransformerClassesForQuery: [],
            defaultDTOConstructorClassForQuery: SerializerDTOConstructor::class,
            defaultDTOValidatorClassesForQuery: [],
            defaultHandlerWrapperClassesForQuery: [],
            defaultResponseConstructorClassForQuery: SerializerJsonResponseConstructor::class,
        );
    }

    /**
     * @test
     *
     * @covers ::handle
     */
    public function query_controller_works_with_all_components(): void
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
        $tasksInMemoryRepository = new TasksInMemoryRepository([
            new Task(
                TaskId::generateRandom(),
                $authenticatedUserId,
                'Finish all tests',
            ),
            new Task(
                TaskId::generateRandom(),
                $authenticatedUserId,
                'Improve documentation',
            ),
        ]);
        $lockSimulator = new LockSimulator();

        $securitySimulator = new SecuritySimulator();
        $securitySimulator->fixateAuthenticatedUserId($authenticatedUserId);

        $controller = new QueryController(
            ServiceMapHelper::serviceMap(
                requestValidators: [
                    new GuardAgainstFileWithVirusRequestValidator(new VirusScannerSimulator()),
                ],
                requestDecoders: [
                    new JsonRequestDecoder(),
                ],
                requestDataTransformers: [
                    new AddActionIdRequestDataTransformer(),
                ],
                dtoConstructors: [
                    new SerializerDTOConstructor($serializer),
                ],
                dtoValidators: [
                    new UserIdValidator($securitySimulator),
                ],
                handlerWrappers: [
                    new GetTasksHandlerWrapper($lockSimulator),
                ],
                queryHandlers: [
                    new GetTasksQueryHandler($tasksInMemoryRepository),
                ],
                responseConstructors: [
                    new SerializerJsonResponseConstructor($serializer),
                ],
            ),
            $this->defaultRouteConfigurationBuilder,
        );

        $content = [
            'userId' => $authenticatedUserId->normalize(),
        ];

        $request = new Request(content: json_encode($content, JSON_THROW_ON_ERROR));
        $routePayload = RoutePayload::generatePayload(
            dtoClass: GetTasksQuery::class,
            handlerClass: GetTasksQueryHandler::class,
            requestValidatorClasses: [
                GuardAgainstFileWithVirusRequestValidator::class => null,
            ],
            requestDataTransformerClasses: [
                AddActionIdRequestDataTransformer::class => null,
            ],
            dtoValidatorClasses: [
                UserIdValidator::class => null,
            ],
            handlerWrapperClasses: [
                GetTasksHandlerWrapper::class => null,
            ],
        );

        // -- Act
        $response = $controller->handle($request, $routePayload);

        // -- Assert
        /** @var string $body */
        $body = $response->getContent();
        $tasks = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        self::assertCount(2, $tasks);
        self::assertCount(1, $lockSimulator->lockedActions);
        self::assertCount(1, $lockSimulator->unlockedActions);
    }

    /**
     * @test
     *
     * @covers ::handle
     */
    public function query_controller_works_with_handler_wrapper_in_catch_case_with_multiple_catches(): void
    {
        // -- Assert
        $this->expectException(TasksNotAccessible::class);

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
        $lockSimulator = new LockSimulator();

        $securitySimulator = new SecuritySimulator();
        $securitySimulator->fixateAuthenticatedUserId($authenticatedUserId);

        $controller = new QueryController(
            ServiceMapHelper::serviceMap(
                requestDecoders: [
                    new JsonRequestDecoder(),
                ],
                requestDataTransformers: [
                    new AddActionIdRequestDataTransformer(),
                ],
                dtoConstructors: [
                    new SerializerDTOConstructor($serializer),
                ],
                dtoValidators: [
                    new UserIdValidator($securitySimulator),
                ],
                handlerWrappers: [
                    new GetTasksHandlerWrapper($lockSimulator),
                ],
                queryHandlers: [
                    new FailingGetTasksQueryHandler(),
                ],
                responseConstructors: [
                    new SerializerJsonResponseConstructor($serializer),
                ],
            ),
            $this->defaultRouteConfigurationBuilder,
        );

        $content = [
            'userId' => (string) $authenticatedUserId,
        ];

        $request = new Request(content: json_encode($content, JSON_THROW_ON_ERROR));
        $routePayload = RoutePayload::generatePayload(
            dtoClass: GetTasksQuery::class,
            handlerClass: FailingGetTasksQueryHandler::class,
            requestDataTransformerClasses: [
                AddActionIdRequestDataTransformer::class => null,
            ],
            dtoValidatorClasses: [
                UserIdValidator::class => null,
            ],
            handlerWrapperClasses: [
                GetTasksHandlerWrapper::class => null,
            ],
        );

        // -- Act
        $controller->handle($request, $routePayload);
    }
}
