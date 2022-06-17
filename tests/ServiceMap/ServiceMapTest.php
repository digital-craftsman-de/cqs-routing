<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ServiceMap;

use DigitalCraftsman\CQRS\RequestDecoder\JsonRequestDecoder;
use DigitalCraftsman\CQRS\ResponseConstructor\EmptyJsonResponseConstructor;
use DigitalCraftsman\CQRS\ResponseConstructor\EmptyResponseConstructor;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredCommandHandlerNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredQueryHandlerNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredRequestDecoderNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredResponseConstructorNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\RequestDecoderOrDefaultRequestDecoderMustBeConfigured;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ResponseConstructorOrDefaultResponseConstructorMustBeConfigured;
use DigitalCraftsman\CQRS\Test\Domain\Tasks\ReadSide\GetTasks\GetTasksQueryHandler;
use DigitalCraftsman\CQRS\Test\Domain\Tasks\WriteSide\CreateTask\CreateTaskCommandHandler;
use DigitalCraftsman\CQRS\Test\Domain\Tasks\WriteSide\CreateTask\CreateTaskRequestDecoder;
use PHPUnit\Framework\TestCase;

/** @coversDefaultClass \DigitalCraftsman\CQRS\ServiceMap\ServiceMap */
final class ServiceMapTest extends TestCase
{
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
