<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ServiceMap;

use DigitalCraftsman\CQRS\DTOConstructor\SerializerDTOConstructor;
use DigitalCraftsman\CQRS\RequestDecoder\JsonRequestDecoder;
use DigitalCraftsman\CQRS\ResponseConstructor\EmptyJsonResponseConstructor;
use DigitalCraftsman\CQRS\ResponseConstructor\EmptyResponseConstructor;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredCommandHandlerNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredDTOConstructorNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredDTODataTransformerNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredQueryHandlerNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredRequestDecoderNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredResponseConstructorNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\DTOConstructorOrDefaultDTOConstructorMustBeConfigured;
use DigitalCraftsman\CQRS\ServiceMap\Exception\RequestDecoderOrDefaultRequestDecoderMustBeConfigured;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ResponseConstructorOrDefaultResponseConstructorMustBeConfigured;
use DigitalCraftsman\CQRS\Test\Application\AddActionHashDTODataTransformer;
use DigitalCraftsman\CQRS\Test\Domain\News\WriteSide\CreateNewsArticle\CreateNewsArticleDTODataTransformer;
use DigitalCraftsman\CQRS\Test\Domain\Tasks\ReadSide\GetTasks\GetTasksQueryHandler;
use DigitalCraftsman\CQRS\Test\Domain\Tasks\WriteSide\CreateTask\CreateTaskCommandHandler;
use DigitalCraftsman\CQRS\Test\Domain\Tasks\WriteSide\CreateTask\CreateTaskDTOConstructor;
use DigitalCraftsman\CQRS\Test\Domain\Tasks\WriteSide\CreateTask\CreateTaskRequestDecoder;
use DigitalCraftsman\CQRS\Test\Domain\Tasks\WriteSide\DefineTaskHourContingent\DefineTaskHourContingentDTODataTransformer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

/** @coversDefaultClass \DigitalCraftsman\CQRS\ServiceMap\ServiceMap */
final class ServiceMapTest extends TestCase
{
    private DenormalizerInterface $serializer;

    public function setUp(): void
    {
        parent::setUp();

        $this->serializer = new Serializer([
            new PropertyNormalizer(),
        ], [
            new JsonEncoder(),
        ]);
    }

    // -- Request decoders

    /**
     * @test
     * @covers ::getRequestDecoder
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
            $defaultDataTransformer = new AddActionHashDTODataTransformer(),
        ];
        $serviceMap = new ServiceMap(dtoDataTransformers: $allDTODataTransformers);

        // -- Act
        $dtoDataTransformers = $serviceMap->getDTODataTransformers(
            null,
            [AddActionHashDTODataTransformer::class],
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
            new AddActionHashDTODataTransformer(),
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
            [AddActionHashDTODataTransformer::class],
        );
    }

    // -- DTO constructors

    /**
     * @test
     * @covers ::getDTOConstructor
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

    // -- Command handler

    /**
     * @test
     * @covers ::getCommandHandler
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
