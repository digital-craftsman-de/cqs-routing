<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ServiceMap;

use DigitalCraftsman\CQRS\DTO\HandlerWrapperConfiguration;
use DigitalCraftsman\CQRS\DTOConstructor\SerializerDTOConstructor;
use DigitalCraftsman\CQRS\RequestDecoder\JsonRequestDecoder;
use DigitalCraftsman\CQRS\ResponseConstructor\EmptyJsonResponseConstructor;
use DigitalCraftsman\CQRS\ResponseConstructor\EmptyResponseConstructor;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredCommandHandlerNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredDTOConstructorNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredDTODataTransformerNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredDTOValidatorNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredHandlerWrapperNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredQueryHandlerNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredRequestDecoderNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredResponseConstructorNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\DTOConstructorOrDefaultDTOConstructorMustBeConfigured;
use DigitalCraftsman\CQRS\ServiceMap\Exception\RequestDecoderOrDefaultRequestDecoderMustBeConfigured;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ResponseConstructorOrDefaultResponseConstructorMustBeConfigured;
use DigitalCraftsman\CQRS\Test\Application\AddActionIdDTODataTransformer;
use DigitalCraftsman\CQRS\Test\Application\Authentication\UserIdValidator;
use DigitalCraftsman\CQRS\Test\Application\ConnectionTransactionWrapper;
use DigitalCraftsman\CQRS\Test\Application\FileSizeValidator;
use DigitalCraftsman\CQRS\Test\Application\SilentExceptionWrapper;
use DigitalCraftsman\CQRS\Test\AppTestCase;
use DigitalCraftsman\CQRS\Test\Domain\News\WriteSide\CreateNewsArticle\CreateNewsArticleDTODataTransformer;
use DigitalCraftsman\CQRS\Test\Domain\Tasks\ReadSide\GetTasks\GetTasksQueryHandler;
use DigitalCraftsman\CQRS\Test\Domain\Tasks\WriteSide\CreateTask\CreateTaskCommandHandler;
use DigitalCraftsman\CQRS\Test\Domain\Tasks\WriteSide\CreateTask\CreateTaskDTOConstructor;
use DigitalCraftsman\CQRS\Test\Domain\Tasks\WriteSide\CreateTask\CreateTaskRequestDecoder;
use DigitalCraftsman\CQRS\Test\Domain\Tasks\WriteSide\DefineTaskHourContingent\DefineTaskHourContingentDTODataTransformer;
use DigitalCraftsman\CQRS\Test\Domain\Tasks\WriteSide\MarkTaskAsAccepted\Exception\TaskAlreadyAccepted;
use DigitalCraftsman\CQRS\Test\Domain\Tasks\WriteSide\MarkTaskAsAccepted\MarkTaskAsAcceptedCommandHandler;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/** @coversDefaultClass \DigitalCraftsman\CQRS\ServiceMap\ServiceMap */
final class ServiceMapTest extends AppTestCase
{
    private DenormalizerInterface $serializer;
    private Security $security;
    private Connection $connection;

    public function setUp(): void
    {
        parent::setUp();

        $this->serializer = $this->getContainerService(DenormalizerInterface::class);
        $this->security = $this->getContainerService(Security::class);
        $this->connection = $this->getContainerService(Connection::class);
    }

    // -- Construction

    /**
     * @test
     * @covers ::__construct
     * @doesNotPerformAssertions
     */
    public function construction_works(): void
    {
        // -- Arrange & Act & Assert
        new ServiceMap(
            requestDecoders: [
                new CreateTaskRequestDecoder(),
                new JsonRequestDecoder(),
            ],
            dtoDataTransformers: [
                new CreateNewsArticleDTODataTransformer(),
                new DefineTaskHourContingentDTODataTransformer(),
            ],
            dtoConstructors: [
                new SerializerDTOConstructor($this->serializer),
                new CreateTaskDTOConstructor(),
            ],
            dtoValidators: [
                new FileSizeValidator(10),
                new UserIdValidator($this->security),
            ],
            handlerWrappers: [
                new SilentExceptionWrapper(),
                new ConnectionTransactionWrapper($this->connection),
            ],
            commandHandlers: [
                new CreateTaskCommandHandler(),
                new MarkTaskAsAcceptedCommandHandler(),
            ],
            queryHandlers: [
                new GetTasksQueryHandler(),
            ],
            responseConstructors: [
                new EmptyResponseConstructor(),
                new EmptyJsonResponseConstructor(),
            ],
        );
    }

    // -- Request decoders

    /**
     * @test
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
        $serviceMap = new ServiceMap(requestDecoders: $requestDecoders);

        // -- Act
        $requestDecoder = $serviceMap->getRequestDecoder(CreateTaskRequestDecoder::class, null);

        // -- Assert
        self::assertSame($createTaskRequestDecoder, $requestDecoder);
    }

    /**
     * @test
     * @covers ::getRequestDecoder
     */
    public function get_request_decoder_works_with_default_request_decoder_class(): void
    {
        // -- Arrange
        $requestDecoders = [
            new CreateTaskRequestDecoder(),
            $defaultRequestDecoder = new JsonRequestDecoder(),
        ];
        $serviceMap = new ServiceMap(requestDecoders: $requestDecoders);

        // -- Act
        $requestDecoder = $serviceMap->getRequestDecoder(null, JsonRequestDecoder::class);

        // -- Assert
        self::assertSame($defaultRequestDecoder, $requestDecoder);
    }

    /**
     * @test
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
        $serviceMap = new ServiceMap(requestDecoders: $requestDecoders);

        // -- Act
        $serviceMap->getRequestDecoder(CreateTaskRequestDecoder::class, null);
    }

    /**
     * @test
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
        $serviceMap = new ServiceMap(requestDecoders: $requestDecoders);

        // -- Act
        $serviceMap->getRequestDecoder(null, JsonRequestDecoder::class);
    }

    /**
     * @test
     * @covers ::getRequestDecoder
     */
    public function get_request_decoder_fails_when_no_request_decoder_class_and_default_request_decoder_class_is_defined(): void
    {
        // -- Assert
        $this->expectException(RequestDecoderOrDefaultRequestDecoderMustBeConfigured::class);

        // -- Arrange
        $serviceMap = new ServiceMap(requestDecoders: []);

        // -- Act
        $serviceMap->getRequestDecoder(null, null);
    }

    // -- DTO data transformers

    /**
     * @test
     * @covers ::getDTODataTransformers
     * @covers ::__construct
     */
    public function get_dto_data_transformers_works_with_dto_data_transformer_classes(): void
    {
        // -- Arrange
        $allDTODataTransformers = [
            new CreateNewsArticleDTODataTransformer(),
            $defineTaskHourContingentDTODataTransformer = new DefineTaskHourContingentDTODataTransformer(),
        ];
        $serviceMap = new ServiceMap(dtoDataTransformers: $allDTODataTransformers);

        // -- Act
        $dtoDataTransformers = $serviceMap->getDTODataTransformers(
            [DefineTaskHourContingentDTODataTransformer::class],
            null,
        );

        // -- Assert
        self::assertCount(1, $dtoDataTransformers);
        self::assertContains($defineTaskHourContingentDTODataTransformer, $dtoDataTransformers);
    }

    /**
     * @test
     * @covers ::getDTODataTransformers
     */
    public function get_dto_data_transformers_works_with_default_dto_data_transformer_classes(): void
    {
        // -- Arrange
        $allDTODataTransformers = [
            new CreateNewsArticleDTODataTransformer(),
            new DefineTaskHourContingentDTODataTransformer(),
            $defaultDataTransformer = new AddActionIdDTODataTransformer(),
        ];
        $serviceMap = new ServiceMap(dtoDataTransformers: $allDTODataTransformers);

        // -- Act
        $dtoDataTransformers = $serviceMap->getDTODataTransformers(
            null,
            [AddActionIdDTODataTransformer::class],
        );

        // -- Assert
        self::assertCount(1, $dtoDataTransformers);
        self::assertContains($defaultDataTransformer, $dtoDataTransformers);
    }

    /**
     * @test
     * @covers ::getDTODataTransformers
     */
    public function get_dto_data_transformers_works_with_no_dto_data_transformer_classes_and_no_default_dto_data_transformer_classes(): void
    {
        // -- Arrange
        $allDTODataTransformers = [
            new CreateNewsArticleDTODataTransformer(),
            new DefineTaskHourContingentDTODataTransformer(),
        ];
        $serviceMap = new ServiceMap(dtoDataTransformers: $allDTODataTransformers);

        // -- Act
        $dtoDataTransformers = $serviceMap->getDTODataTransformers(
            null,
            null,
        );

        // -- Assert
        self::assertCount(0, $dtoDataTransformers);
    }

    /**
     * @test
     * @covers ::getDTODataTransformers
     */
    public function get_dto_data_transformers_fails_when_dto_data_transformer_classes_are_not_available(): void
    {
        // -- Assert
        $this->expectException(ConfiguredDTODataTransformerNotAvailable::class);

        // -- Arrange
        $allDTODataTransformers = [
            new CreateNewsArticleDTODataTransformer(),
            new AddActionIdDTODataTransformer(),
        ];
        $serviceMap = new ServiceMap(dtoDataTransformers: $allDTODataTransformers);

        // -- Act
        $serviceMap->getDTODataTransformers(
            [DefineTaskHourContingentDTODataTransformer::class],
            null,
        );
    }

    /**
     * @test
     * @covers ::getDTODataTransformers
     */
    public function get_dto_data_transformers_fails_when_default_dto_data_transformer_classes_are_not_available(): void
    {
        // -- Assert
        $this->expectException(ConfiguredDTODataTransformerNotAvailable::class);

        // -- Arrange
        $allDTODataTransformers = [
            new CreateNewsArticleDTODataTransformer(),
            new DefineTaskHourContingentDTODataTransformer(),
        ];
        $serviceMap = new ServiceMap(dtoDataTransformers: $allDTODataTransformers);

        // -- Act
        $serviceMap->getDTODataTransformers(
            null,
            [AddActionIdDTODataTransformer::class],
        );
    }

    // -- DTO constructors

    /**
     * @test
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
        $serviceMap = new ServiceMap(dtoConstructors: $dtoConstructors);

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
     * @covers ::getDTOConstructor
     */
    public function get_dto_constructor_works_with_default_dto_constructor_class(): void
    {
        // -- Arrange
        $dtoConstructors = [
            $defaultDTOConstructor = new SerializerDTOConstructor($this->serializer),
            new CreateTaskDTOConstructor(),
        ];
        $serviceMap = new ServiceMap(dtoConstructors: $dtoConstructors);

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
        $serviceMap = new ServiceMap(dtoConstructors: $dtoConstructors);

        // -- Act
        $serviceMap->getDTOConstructor(CreateTaskDTOConstructor::class, null);
    }

    /**
     * @test
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
        $serviceMap = new ServiceMap(dtoConstructors: $dtoConstructors);

        // -- Act
        $serviceMap->getDTOConstructor(null, SerializerDTOConstructor::class);
    }

    /**
     * @test
     * @covers ::getDTOConstructor
     */
    public function get_dto_constructor_fails_when_no_dto_constructor_class_and_default_dto_constructor_class_is_defined(): void
    {
        // -- Assert
        $this->expectException(DTOConstructorOrDefaultDTOConstructorMustBeConfigured::class);

        // -- Arrange
        $serviceMap = new ServiceMap(dtoConstructors: []);

        // -- Act
        $serviceMap->getDTOConstructor(null, null);
    }

    // -- DTO validators

    /**
     * @test
     * @covers ::getDTOValidators
     * @covers ::__construct
     */
    public function get_dto_validators_works_with_dto_validators_classes(): void
    {
        // -- Arrange
        $allDTOValidators = [
            $fileSizeValidator = new FileSizeValidator(10),
            new UserIdValidator($this->security),
        ];
        $serviceMap = new ServiceMap(dtoValidators: $allDTOValidators);

        // -- Act
        $dtoValidators = $serviceMap->getDTOValidators(
            [FileSizeValidator::class],
            null,
        );

        // -- Assert
        self::assertCount(1, $dtoValidators);
        self::assertContains($fileSizeValidator, $dtoValidators);
    }

    /**
     * @test
     * @covers ::getDTOValidators
     */
    public function get_dto_validators_works_with_default_dto_validator_classes(): void
    {
        // -- Arrange
        $allDTOValidators = [
            new FileSizeValidator(10),
            $defaultDataTransformer = new UserIdValidator($this->security),
        ];
        $serviceMap = new ServiceMap(dtoValidators: $allDTOValidators);

        // -- Act
        $dtoValidators = $serviceMap->getDTOValidators(
            null,
            [UserIdValidator::class],
        );

        // -- Assert
        self::assertCount(1, $dtoValidators);
        self::assertContains($defaultDataTransformer, $dtoValidators);
    }

    /**
     * @test
     * @covers ::getDTOValidators
     */
    public function get_dto_validators_works_with_no_dto_validator_classes_and_no_default_dto_validator_classes(): void
    {
        // -- Arrange
        $allDTOValidators = [
            new FileSizeValidator(10),
            new UserIdValidator($this->security),
        ];
        $serviceMap = new ServiceMap(dtoValidators: $allDTOValidators);

        // -- Act
        $dtoValidators = $serviceMap->getDTOValidators(
            null,
            null,
        );

        // -- Assert
        self::assertCount(0, $dtoValidators);
    }

    /**
     * @test
     * @covers ::getDTOValidators
     */
    public function get_dto_validators_fails_when_dto_validator_classes_are_not_available(): void
    {
        // -- Assert
        $this->expectException(ConfiguredDTOValidatorNotAvailable::class);

        // -- Arrange
        $allDTOValidators = [
            new UserIdValidator($this->security),
        ];
        $serviceMap = new ServiceMap(dtoValidators: $allDTOValidators);

        // -- Act
        $serviceMap->getDTOValidators(
            [FileSizeValidator::class],
            null,
        );
    }

    /**
     * @test
     * @covers ::getDTOValidators
     */
    public function get_dto_validators_fails_when_default_dto_data_transformer_classes_are_not_available(): void
    {
        // -- Assert
        $this->expectException(ConfiguredDTOValidatorNotAvailable::class);

        // -- Arrange
        $allDTOValidators = [
            new FileSizeValidator(10),
        ];
        $serviceMap = new ServiceMap(dtoValidators: $allDTOValidators);

        // -- Act
        $serviceMap->getDTOValidators(
            null,
            [UserIdValidator::class],
        );
    }

    // -- Handler wrappers

    /**
     * @test
     * @covers ::getHandlerWrappersWithParameters
     * @covers ::__construct
     */
    public function get_handler_wrappers_works_with_handler_wrapper_configurations(): void
    {
        // -- Arrange
        $handlerWrappers = [
            new SilentExceptionWrapper(),
            new ConnectionTransactionWrapper($this->connection),
        ];
        $serviceMap = new ServiceMap(handlerWrappers: $handlerWrappers);

        $handlerWrapperConfiguration = new HandlerWrapperConfiguration(
            SilentExceptionWrapper::class,
            [
                TaskAlreadyAccepted::class,
            ],
        );

        // -- Act
        $handlerWrappersWithParameters = $serviceMap->getHandlerWrappersWithParameters(
            [$handlerWrapperConfiguration],
            null,
        );

        // -- Assert
        self::assertCount(1, $handlerWrappersWithParameters);
        self::assertSame(SilentExceptionWrapper::class, $handlerWrappersWithParameters[0]->handlerWrapper::class);
        self::assertSame([TaskAlreadyAccepted::class], $handlerWrappersWithParameters[0]->parameters);
    }

    /**
     * @test
     * @covers ::getHandlerWrappersWithParameters
     */
    public function get_handler_wrappers_works_with_default_handler_wrapper_classes(): void
    {
        // -- Arrange
        $handlerWrappers = [
            new SilentExceptionWrapper(),
            new ConnectionTransactionWrapper($this->connection),
        ];
        $serviceMap = new ServiceMap(handlerWrappers: $handlerWrappers);

        // -- Act
        $handlerWrappersWithParameters = $serviceMap->getHandlerWrappersWithParameters(
            null,
            [ConnectionTransactionWrapper::class],
        );

        // -- Assert
        self::assertCount(1, $handlerWrappersWithParameters);
        self::assertSame(ConnectionTransactionWrapper::class, $handlerWrappersWithParameters[0]->handlerWrapper::class);
        self::assertNull($handlerWrappersWithParameters[0]->parameters);
    }

    /**
     * @test
     * @covers ::getHandlerWrappersWithParameters
     */
    public function get_handler_wrappers_works_with_no_handler_wrapper_configurations_and_no_default_handler_wrapper_classes(): void
    {
        // -- Arrange
        $handlerWrappers = [
            new SilentExceptionWrapper(),
            new ConnectionTransactionWrapper($this->connection),
        ];
        $serviceMap = new ServiceMap(handlerWrappers: $handlerWrappers);

        // -- Act
        $handlerWrappersWithParameters = $serviceMap->getHandlerWrappersWithParameters(
            null,
            null,
        );

        // -- Assert
        self::assertCount(0, $handlerWrappersWithParameters);
    }

    /**
     * @test
     * @covers ::getHandlerWrappersWithParameters
     */
    public function get_handler_wrappers_fails_when_handler_wrapper_in_configuration_is_not_available(): void
    {
        // -- Assert
        $this->expectException(ConfiguredHandlerWrapperNotAvailable::class);

        // -- Arrange
        $handlerWrappers = [
            new ConnectionTransactionWrapper($this->connection),
        ];
        $serviceMap = new ServiceMap(handlerWrappers: $handlerWrappers);

        $handlerWrapperConfiguration = new HandlerWrapperConfiguration(
            SilentExceptionWrapper::class,
            [
                TaskAlreadyAccepted::class,
            ],
        );

        // -- Act
        $serviceMap->getHandlerWrappersWithParameters(
            [$handlerWrapperConfiguration],
            null,
        );
    }

    /**
     * @test
     * @covers ::getHandlerWrappersWithParameters
     */
    public function get_handler_wrappers_fails_when_default_handler_wrapper_classes_are_not_available(): void
    {
        // -- Assert
        $this->expectException(ConfiguredHandlerWrapperNotAvailable::class);

        // -- Arrange
        $handlerWrappers = [
            new SilentExceptionWrapper(),
        ];
        $serviceMap = new ServiceMap(handlerWrappers: $handlerWrappers);

        // -- Act
        $serviceMap->getHandlerWrappersWithParameters(
            null,
            [ConnectionTransactionWrapper::class],
        );
    }

    // -- Command handler

    /**
     * @test
     * @covers ::getCommandHandler
     * @covers ::__construct
     */
    public function get_command_handler_works(): void
    {
        // -- Arrange
        $commandHandlers = [
            $createTaskCommandHandler = new CreateTaskCommandHandler(),
        ];
        $serviceMap = new ServiceMap(commandHandlers: $commandHandlers);

        // -- Act
        $commandHandler = $serviceMap->getCommandHandler(CreateTaskCommandHandler::class);

        // -- Assert
        self::assertSame($createTaskCommandHandler, $commandHandler);
    }

    /**
     * @test
     * @covers ::getCommandHandler
     */
    public function get_command_handler_fails_if_not_available(): void
    {
        // -- Assert
        $this->expectException(ConfiguredCommandHandlerNotAvailable::class);

        // -- Arrange
        $serviceMap = new ServiceMap(commandHandlers: []);

        // -- Act
        $serviceMap->getCommandHandler(CreateTaskCommandHandler::class);
    }

    // -- Query handler

    /**
     * @test
     * @covers ::getQueryHandler
     * @covers ::__construct
     */
    public function get_query_handler_works(): void
    {
        // -- Arrange
        $queryHandlers = [
            $getTasksQueryHandler = new GetTasksQueryHandler(),
        ];
        $serviceMap = new ServiceMap(queryHandlers: $queryHandlers);

        // -- Act
        $queryHandler = $serviceMap->getQueryHandler(GetTasksQueryHandler::class);

        // -- Assert
        self::assertSame($getTasksQueryHandler, $queryHandler);
    }

    /**
     * @test
     * @covers ::getQueryHandler
     */
    public function get_query_handler_fails_if_not_available(): void
    {
        // -- Assert
        $this->expectException(ConfiguredQueryHandlerNotAvailable::class);

        // -- Arrange
        $serviceMap = new ServiceMap(queryHandlers: []);

        // -- Act
        $serviceMap->getQueryHandler(GetTasksQueryHandler::class);
    }

    // -- Response constructors

    /**
     * @test
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
        $serviceMap = new ServiceMap(responseConstructors: $responseConstructors);

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
     * @covers ::getResponseConstructor
     */
    public function get_response_constructor_works_with_default_response_constructor_class(): void
    {
        // -- Arrange
        $responseConstructors = [
            new EmptyResponseConstructor(),
            $defaultResponseConstructor = new EmptyJsonResponseConstructor(),
        ];
        $serviceMap = new ServiceMap(responseConstructors: $responseConstructors);

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
        $serviceMap = new ServiceMap(responseConstructors: $responseConstructors);

        // -- Act
        $serviceMap->getResponseConstructor(EmptyResponseConstructor::class, null);
    }

    /**
     * @test
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
        $serviceMap = new ServiceMap(responseConstructors: $responseConstructors);

        // -- Act
        $serviceMap->getResponseConstructor(null, EmptyJsonResponseConstructor::class);
    }

    /**
     * @test
     * @covers ::getResponseConstructor
     */
    public function get_response_constructor_fails_when_no_response_constructor_class_and_default_response_constructor_class_is_defined(): void
    {
        // -- Assert
        $this->expectException(ResponseConstructorOrDefaultResponseConstructorMustBeConfigured::class);

        // -- Arrange
        $serviceMap = new ServiceMap(responseConstructors: []);

        // -- Act
        $serviceMap->getResponseConstructor(null, null);
    }
}