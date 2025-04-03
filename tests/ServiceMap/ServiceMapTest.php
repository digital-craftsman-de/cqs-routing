<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\ServiceMap;

use DigitalCraftsman\CQSRouting\DTOConstructor\SerializerDTOConstructor;
use DigitalCraftsman\CQSRouting\HandlerWrapper\SilentExceptionWrapper;
use DigitalCraftsman\CQSRouting\RequestDecoder\JsonRequestDecoder;
use DigitalCraftsman\CQSRouting\ResponseConstructor\EmptyJsonResponseConstructor;
use DigitalCraftsman\CQSRouting\ResponseConstructor\EmptyResponseConstructor;
use DigitalCraftsman\CQSRouting\ServiceMap\Exception\ConfiguredCommandHandlerNotAvailable;
use DigitalCraftsman\CQSRouting\ServiceMap\Exception\ConfiguredDTOConstructorNotAvailable;
use DigitalCraftsman\CQSRouting\ServiceMap\Exception\ConfiguredDTOValidatorNotAvailable;
use DigitalCraftsman\CQSRouting\ServiceMap\Exception\ConfiguredHandlerWrapperNotAvailable;
use DigitalCraftsman\CQSRouting\ServiceMap\Exception\ConfiguredQueryHandlerNotAvailable;
use DigitalCraftsman\CQSRouting\ServiceMap\Exception\ConfiguredRequestDataTransformerNotAvailable;
use DigitalCraftsman\CQSRouting\ServiceMap\Exception\ConfiguredRequestDecoderNotAvailable;
use DigitalCraftsman\CQSRouting\ServiceMap\Exception\ConfiguredRequestValidatorNotAvailable;
use DigitalCraftsman\CQSRouting\ServiceMap\Exception\ConfiguredResponseConstructorNotAvailable;
use DigitalCraftsman\CQSRouting\ServiceMap\Exception\DTOConstructorOrDefaultDTOConstructorMustBeConfigured;
use DigitalCraftsman\CQSRouting\ServiceMap\Exception\RequestDecoderOrDefaultRequestDecoderMustBeConfigured;
use DigitalCraftsman\CQSRouting\ServiceMap\Exception\ResponseConstructorOrDefaultResponseConstructorMustBeConfigured;
use DigitalCraftsman\CQSRouting\Test\Application\AddActionIdRequestDataTransformer;
use DigitalCraftsman\CQSRouting\Test\Application\Authentication\UserIdValidator;
use DigitalCraftsman\CQSRouting\Test\Application\ConnectionTransactionWrapper;
use DigitalCraftsman\CQSRouting\Test\Application\FileSizeValidator;
use DigitalCraftsman\CQSRouting\Test\AppTestCase;
use DigitalCraftsman\CQSRouting\Test\Domain\News\WriteSide\CreateNewsArticle\CreateNewsArticleRequestDataTransformer;
use DigitalCraftsman\CQSRouting\Test\Domain\Tasks\ReadSide\GetTasks\GetTasksQueryHandler;
use DigitalCraftsman\CQSRouting\Test\Domain\Tasks\WriteSide\CreateTask\CreateTaskCommandHandler;
use DigitalCraftsman\CQSRouting\Test\Domain\Tasks\WriteSide\CreateTask\CreateTaskDTOConstructor;
use DigitalCraftsman\CQSRouting\Test\Domain\Tasks\WriteSide\CreateTask\CreateTaskRequestDecoder;
use DigitalCraftsman\CQSRouting\Test\Domain\Tasks\WriteSide\DefineTaskHourContingent\DefineTaskHourContingentRequestDataTransformer;
use DigitalCraftsman\CQSRouting\Test\Domain\Tasks\WriteSide\MarkTaskAsAccepted\MarkTaskAsAcceptedCommandHandler;
use DigitalCraftsman\CQSRouting\Test\Helper\ServiceMapHelper;
use DigitalCraftsman\CQSRouting\Test\Repository\TasksInMemoryRepository;
use DigitalCraftsman\CQSRouting\Test\RequestValidator\GuardAgainstFileWithVirusRequestValidator;
use DigitalCraftsman\CQSRouting\Test\RequestValidator\GuardAgainstTokenInHeaderRequestValidator;
use DigitalCraftsman\CQSRouting\Test\Utility\ConnectionSimulator;
use DigitalCraftsman\CQSRouting\Test\Utility\SecuritySimulator;
use DigitalCraftsman\CQSRouting\Test\Utility\ServiceLocatorSimulator;
use DigitalCraftsman\CQSRouting\Test\Utility\VirusScannerSimulator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/** @coversDefaultClass \DigitalCraftsman\CQSRouting\ServiceMap\ServiceMap */
final class ServiceMapTest extends AppTestCase
{
    private DenormalizerInterface $serializer;
    private SecuritySimulator $securitySimulator;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->serializer = $this->getContainerService(DenormalizerInterface::class);
        $this->securitySimulator = new SecuritySimulator();
    }

    // -- Construction

    /**
     * @test
     *
     * @covers ::__construct
     *
     * @doesNotPerformAssertions
     *
     * @noinspection PhpExpressionResultUnusedInspection
     */
    public function construction_works(): void
    {
        // -- Arrange & Act & Assert
        new ServiceMap(
            requestValidators: new ServiceLocatorSimulator([
                GuardAgainstTokenInHeaderRequestValidator::class => new GuardAgainstTokenInHeaderRequestValidator(),
            ]),
            requestDecoders: new ServiceLocatorSimulator([
                CreateTaskRequestDecoder::class => new CreateTaskRequestDecoder(),
                JsonRequestDecoder::class => new JsonRequestDecoder(),
            ]),
            requestDataTransformers: new ServiceLocatorSimulator([
                CreateNewsArticleRequestDataTransformer::class => new CreateNewsArticleRequestDataTransformer(),
                DefineTaskHourContingentRequestDataTransformer::class => new DefineTaskHourContingentRequestDataTransformer(),
            ]),
            dtoConstructors: new ServiceLocatorSimulator([
                SerializerDTOConstructor::class => new SerializerDTOConstructor($this->serializer),
                CreateTaskDTOConstructor::class => new CreateTaskDTOConstructor(),
            ]),
            dtoValidators: new ServiceLocatorSimulator([
                FileSizeValidator::class => new FileSizeValidator(),
                UserIdValidator::class => new UserIdValidator($this->securitySimulator),
            ]),
            handlerWrappers: new ServiceLocatorSimulator([
                SilentExceptionWrapper::class => new SilentExceptionWrapper(),
                ConnectionTransactionWrapper::class => new ConnectionTransactionWrapper(new ConnectionSimulator()),
            ]),
            commandHandlers: new ServiceLocatorSimulator([
                CreateTaskCommandHandler::class => new CreateTaskCommandHandler(),
                MarkTaskAsAcceptedCommandHandler::class => new MarkTaskAsAcceptedCommandHandler(),
            ]),
            queryHandlers: new ServiceLocatorSimulator([
                GetTasksQueryHandler::class => new GetTasksQueryHandler(new TasksInMemoryRepository()),
            ]),
            responseConstructors: new ServiceLocatorSimulator([
                EmptyResponseConstructor::class => new EmptyResponseConstructor(),
                EmptyJsonResponseConstructor::class => new EmptyJsonResponseConstructor(),
            ]),
        );
    }

    // -- Request validators

    /**
     * @test
     *
     * @covers ::getRequestValidator
     * @covers ::__construct
     */
    public function get_request_validator_works_with_request_validator_classes(): void
    {
        // -- Arrange
        $allRequestValidators = [
            new GuardAgainstTokenInHeaderRequestValidator(),
            new GuardAgainstFileWithVirusRequestValidator(new VirusScannerSimulator()),
        ];
        $serviceMap = ServiceMapHelper::serviceMap(requestValidators: $allRequestValidators);

        // -- Act
        $requestValidator = $serviceMap->getRequestValidator(GuardAgainstFileWithVirusRequestValidator::class);

        // -- Assert
        self::assertSame(GuardAgainstFileWithVirusRequestValidator::class, $requestValidator::class);
    }

    /**
     * @test
     *
     * @covers ::getRequestValidator
     */
    public function get_request_validator_fails_when_request_validator_class_is_not_available(): void
    {
        // -- Assert
        $this->expectException(ConfiguredRequestValidatorNotAvailable::class);

        // -- Arrange
        $allRequestValidators = [
            new GuardAgainstTokenInHeaderRequestValidator(),
        ];
        $serviceMap = ServiceMapHelper::serviceMap(requestValidators: $allRequestValidators);

        // -- Act
        $serviceMap->getRequestValidator(GuardAgainstFileWithVirusRequestValidator::class);
    }

    // -- Request decoders

    /**
     * @test
     *
     * @covers ::getRequestDecoder
     * @covers ::__construct
     */
    public function get_request_decoder_works_with_request_decoder_class(): void
    {
        // -- Arrange
        $requestDecoders = [
            $createTaskRequestDecoder = new CreateTaskRequestDecoder(),
            new JsonRequestDecoder(),
        ];
        $serviceMap = ServiceMapHelper::serviceMap(requestDecoders: $requestDecoders);

        // -- Act
        $requestDecoder = $serviceMap->getRequestDecoder(CreateTaskRequestDecoder::class, null);

        // -- Assert
        self::assertSame($createTaskRequestDecoder, $requestDecoder);
    }

    /**
     * @test
     *
     * @covers ::getRequestDecoder
     */
    public function get_request_decoder_works_with_default_request_decoder_class(): void
    {
        // -- Arrange
        $requestDecoders = [
            new CreateTaskRequestDecoder(),
            $defaultRequestDecoder = new JsonRequestDecoder(),
        ];
        $serviceMap = ServiceMapHelper::serviceMap(requestDecoders: $requestDecoders);

        // -- Act
        $requestDecoder = $serviceMap->getRequestDecoder(null, JsonRequestDecoder::class);

        // -- Assert
        self::assertSame($defaultRequestDecoder, $requestDecoder);
    }

    /**
     * @test
     *
     * @covers ::getRequestDecoder
     */
    public function get_request_decoder_fails_when_request_decoder_class_is_not_available(): void
    {
        // -- Assert
        $this->expectException(ConfiguredRequestDecoderNotAvailable::class);

        // -- Arrange
        $requestDecoders = [
            new JsonRequestDecoder(),
        ];
        $serviceMap = ServiceMapHelper::serviceMap(requestDecoders: $requestDecoders);

        // -- Act
        $serviceMap->getRequestDecoder(CreateTaskRequestDecoder::class, null);
    }

    /**
     * @test
     *
     * @covers ::getRequestDecoder
     */
    public function get_request_decoder_fails_when_default_request_decoder_class_is_not_available(): void
    {
        // -- Assert
        $this->expectException(ConfiguredRequestDecoderNotAvailable::class);

        // -- Arrange
        $requestDecoders = [
            new CreateTaskRequestDecoder(),
        ];
        $serviceMap = ServiceMapHelper::serviceMap(requestDecoders: $requestDecoders);

        // -- Act
        $serviceMap->getRequestDecoder(null, JsonRequestDecoder::class);
    }

    /**
     * @test
     *
     * @covers ::getRequestDecoder
     */
    public function get_request_decoder_fails_when_no_request_decoder_class_and_default_request_decoder_class_is_defined(): void
    {
        // -- Assert
        $this->expectException(RequestDecoderOrDefaultRequestDecoderMustBeConfigured::class);

        // -- Arrange
        $serviceMap = ServiceMapHelper::serviceMap(requestDecoders: []);

        // -- Act
        $serviceMap->getRequestDecoder(null, null);
    }

    // -- Request data transformers

    /**
     * @test
     *
     * @covers ::getRequestDataTransformer
     * @covers ::__construct
     */
    public function get_request_data_transformer_works_with_request_data_transformer_classes(): void
    {
        // -- Arrange
        $allRequestDataTransformers = [
            new CreateNewsArticleRequestDataTransformer(),
            new DefineTaskHourContingentRequestDataTransformer(),
        ];
        $serviceMap = ServiceMapHelper::serviceMap(requestDataTransformers: $allRequestDataTransformers);

        // -- Act
        $requestDataTransformer = $serviceMap->getRequestDataTransformer(DefineTaskHourContingentRequestDataTransformer::class);

        // -- Assert
        self::assertSame(DefineTaskHourContingentRequestDataTransformer::class, $requestDataTransformer::class);
    }

    /**
     * @test
     *
     * @covers ::getRequestDataTransformer
     */
    public function get_request_data_transformer_fails_when_request_data_transformer_class_is_not_available(): void
    {
        // -- Assert
        $this->expectException(ConfiguredRequestDataTransformerNotAvailable::class);

        // -- Arrange
        $allRequestDataTransformers = [
            new CreateNewsArticleRequestDataTransformer(),
            new AddActionIdRequestDataTransformer(),
        ];
        $serviceMap = ServiceMapHelper::serviceMap(requestDataTransformers: $allRequestDataTransformers);

        // -- Act
        $serviceMap->getRequestDataTransformer(DefineTaskHourContingentRequestDataTransformer::class);
    }

    // -- DTO constructors

    /**
     * @test
     *
     * @covers ::getDTOConstructor
     * @covers ::__construct
     */
    public function get_dto_constructor_works_with_dto_constructor_class(): void
    {
        // -- Arrange
        $dtoConstructors = [
            new SerializerDTOConstructor($this->serializer),
            $createTaskDTOConstructor = new CreateTaskDTOConstructor(),
        ];
        $serviceMap = ServiceMapHelper::serviceMap(dtoConstructors: $dtoConstructors);

        // -- Act
        $dtoConstructor = $serviceMap->getDTOConstructor(
            CreateTaskDTOConstructor::class,
            null,
        );

        // -- Assert
        self::assertSame($createTaskDTOConstructor, $dtoConstructor);
    }

    /**
     * @test
     *
     * @covers ::getDTOConstructor
     */
    public function get_dto_constructor_works_with_default_dto_constructor_class(): void
    {
        // -- Arrange
        $dtoConstructors = [
            $defaultDTOConstructor = new SerializerDTOConstructor($this->serializer),
            new CreateTaskDTOConstructor(),
        ];
        $serviceMap = ServiceMapHelper::serviceMap(dtoConstructors: $dtoConstructors);

        // -- Act
        $dtoConstructor = $serviceMap->getDTOConstructor(
            null,
            SerializerDTOConstructor::class,
        );

        // -- Assert
        self::assertSame($defaultDTOConstructor, $dtoConstructor);
    }

    /**
     * @test
     *
     * @covers ::getDTOConstructor
     */
    public function get_dto_constructor_fails_when_dto_constructor_class_is_not_available(): void
    {
        // -- Assert
        $this->expectException(ConfiguredDTOConstructorNotAvailable::class);

        // -- Arrange
        $dtoConstructors = [
            new SerializerDTOConstructor($this->serializer),
        ];
        $serviceMap = ServiceMapHelper::serviceMap(dtoConstructors: $dtoConstructors);

        // -- Act
        $serviceMap->getDTOConstructor(CreateTaskDTOConstructor::class, null);
    }

    /**
     * @test
     *
     * @covers ::getDTOConstructor
     */
    public function get_dto_constructor_fails_when_default_dto_constructor_class_is_not_available(): void
    {
        // -- Assert
        $this->expectException(ConfiguredDTOConstructorNotAvailable::class);

        // -- Arrange
        $dtoConstructors = [
            new CreateTaskDTOConstructor(),
        ];
        $serviceMap = ServiceMapHelper::serviceMap(dtoConstructors: $dtoConstructors);

        // -- Act
        $serviceMap->getDTOConstructor(null, SerializerDTOConstructor::class);
    }

    /**
     * @test
     *
     * @covers ::getDTOConstructor
     */
    public function get_dto_constructor_fails_when_no_dto_constructor_class_and_default_dto_constructor_class_is_defined(): void
    {
        // -- Assert
        $this->expectException(DTOConstructorOrDefaultDTOConstructorMustBeConfigured::class);

        // -- Arrange
        $serviceMap = ServiceMapHelper::serviceMap(dtoConstructors: []);

        // -- Act
        $serviceMap->getDTOConstructor(null, null);
    }

    // -- DTO validators

    /**
     * @test
     *
     * @covers ::getDTOValidator
     * @covers ::__construct
     */
    public function get_dto_validator_works_with_dto_validators_classes(): void
    {
        // -- Arrange
        $allDTOValidators = [
            new FileSizeValidator(),
            new UserIdValidator($this->securitySimulator),
        ];
        $serviceMap = ServiceMapHelper::serviceMap(dtoValidators: $allDTOValidators);

        // -- Act
        $dtoValidator = $serviceMap->getDTOValidator(FileSizeValidator::class);

        // -- Assert
        self::assertSame(FileSizeValidator::class, $dtoValidator::class);
    }

    /**
     * @test
     *
     * @covers ::getDTOValidator
     */
    public function get_dto_validator_fails_when_dto_validator_class_is_not_available(): void
    {
        // -- Assert
        $this->expectException(ConfiguredDTOValidatorNotAvailable::class);

        // -- Arrange
        $allDTOValidators = [
            new UserIdValidator($this->securitySimulator),
        ];
        $serviceMap = ServiceMapHelper::serviceMap(dtoValidators: $allDTOValidators);

        // -- Act
        $serviceMap->getDTOValidator(FileSizeValidator::class);
    }

    // -- Handler wrappers

    /**
     * @test
     *
     * @covers ::getHandlerWrapper
     * @covers ::__construct
     */
    public function get_handler_wrapper_works(): void
    {
        // -- Arrange
        $handlerWrappers = [
            new SilentExceptionWrapper(),
            new ConnectionTransactionWrapper(new ConnectionSimulator()),
        ];
        $serviceMap = ServiceMapHelper::serviceMap(handlerWrappers: $handlerWrappers);

        // -- Act
        $handlerWrapper = $serviceMap->getHandlerWrapper(ConnectionTransactionWrapper::class);

        // -- Assert
        self::assertSame(ConnectionTransactionWrapper::class, $handlerWrapper::class);
    }

    /**
     * @test
     *
     * @covers ::getHandlerWrapper
     */
    public function get_handler_wrapper_fails_when_handler_wrapper_is_missing(): void
    {
        // -- Assert
        $this->expectException(ConfiguredHandlerWrapperNotAvailable::class);

        // -- Arrange
        $handlerWrappers = [
            new SilentExceptionWrapper(),
        ];
        $serviceMap = ServiceMapHelper::serviceMap(handlerWrappers: $handlerWrappers);

        // -- Act
        $serviceMap->getHandlerWrapper(ConnectionTransactionWrapper::class);
    }

    // -- Command handler

    /**
     * @test
     *
     * @covers ::getCommandHandler
     * @covers ::__construct
     */
    public function get_command_handler_works(): void
    {
        // -- Arrange
        $commandHandlers = [
            $createTaskCommandHandler = new CreateTaskCommandHandler(),
        ];
        $serviceMap = ServiceMapHelper::serviceMap(commandHandlers: $commandHandlers);

        // -- Act
        $commandHandler = $serviceMap->getCommandHandler(CreateTaskCommandHandler::class);

        // -- Assert
        self::assertSame($createTaskCommandHandler, $commandHandler);
    }

    /**
     * @test
     *
     * @covers ::getCommandHandler
     */
    public function get_command_handler_fails_if_not_available(): void
    {
        // -- Assert
        $this->expectException(ConfiguredCommandHandlerNotAvailable::class);

        // -- Arrange
        $serviceMap = ServiceMapHelper::serviceMap(commandHandlers: []);

        // -- Act
        $serviceMap->getCommandHandler(CreateTaskCommandHandler::class);
    }

    // -- Query handler

    /**
     * @test
     *
     * @covers ::getQueryHandler
     * @covers ::__construct
     */
    public function get_query_handler_works(): void
    {
        // -- Arrange
        $queryHandlers = [
            $getTasksQueryHandler = new GetTasksQueryHandler(new TasksInMemoryRepository()),
        ];
        $serviceMap = ServiceMapHelper::serviceMap(queryHandlers: $queryHandlers);

        // -- Act
        $queryHandler = $serviceMap->getQueryHandler(GetTasksQueryHandler::class);

        // -- Assert
        self::assertSame($getTasksQueryHandler, $queryHandler);
    }

    /**
     * @test
     *
     * @covers ::getQueryHandler
     */
    public function get_query_handler_fails_if_not_available(): void
    {
        // -- Assert
        $this->expectException(ConfiguredQueryHandlerNotAvailable::class);

        // -- Arrange
        $serviceMap = ServiceMapHelper::serviceMap(queryHandlers: []);

        // -- Act
        $serviceMap->getQueryHandler(GetTasksQueryHandler::class);
    }

    // -- Response constructors

    /**
     * @test
     *
     * @covers ::getResponseConstructor
     * @covers ::__construct
     */
    public function get_response_constructor_works_with_response_constructor_class(): void
    {
        // -- Arrange
        $responseConstructors = [
            new EmptyResponseConstructor(),
            $emptyJsonResponseConstructor = new EmptyJsonResponseConstructor(),
        ];
        $serviceMap = ServiceMapHelper::serviceMap(responseConstructors: $responseConstructors);

        // -- Act
        $responseConstructor = $serviceMap->getResponseConstructor(
            EmptyJsonResponseConstructor::class,
            null,
        );

        // -- Assert
        self::assertSame($emptyJsonResponseConstructor, $responseConstructor);
    }

    /**
     * @test
     *
     * @covers ::getResponseConstructor
     */
    public function get_response_constructor_works_with_default_response_constructor_class(): void
    {
        // -- Arrange
        $responseConstructors = [
            new EmptyResponseConstructor(),
            $defaultResponseConstructor = new EmptyJsonResponseConstructor(),
        ];
        $serviceMap = ServiceMapHelper::serviceMap(responseConstructors: $responseConstructors);

        // -- Act
        $responseConstructor = $serviceMap->getResponseConstructor(
            null,
            EmptyJsonResponseConstructor::class,
        );

        // -- Assert
        self::assertSame($defaultResponseConstructor, $responseConstructor);
    }

    /**
     * @test
     *
     * @covers ::getResponseConstructor
     */
    public function get_response_constructor_fails_when_response_constructor_class_is_not_available(): void
    {
        // -- Assert
        $this->expectException(ConfiguredResponseConstructorNotAvailable::class);

        // -- Arrange
        $responseConstructors = [
            new EmptyJsonResponseConstructor(),
        ];
        $serviceMap = ServiceMapHelper::serviceMap(responseConstructors: $responseConstructors);

        // -- Act
        $serviceMap->getResponseConstructor(EmptyResponseConstructor::class, null);
    }

    /**
     * @test
     *
     * @covers ::getResponseConstructor
     */
    public function get_response_constructor_fails_when_default_response_constructor_class_is_not_available(): void
    {
        // -- Assert
        $this->expectException(ConfiguredResponseConstructorNotAvailable::class);

        // -- Arrange
        $responseConstructors = [
            new EmptyResponseConstructor(),
        ];
        $serviceMap = ServiceMapHelper::serviceMap(responseConstructors: $responseConstructors);

        // -- Act
        $serviceMap->getResponseConstructor(null, EmptyJsonResponseConstructor::class);
    }

    /**
     * @test
     *
     * @covers ::getResponseConstructor
     */
    public function get_response_constructor_fails_when_no_response_constructor_class_and_default_response_constructor_class_is_defined(): void
    {
        // -- Assert
        $this->expectException(ResponseConstructorOrDefaultResponseConstructorMustBeConfigured::class);

        // -- Arrange
        $serviceMap = ServiceMapHelper::serviceMap(responseConstructors: []);

        // -- Act
        $serviceMap->getResponseConstructor(null, null);
    }
}
