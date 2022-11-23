<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ValueObject;

use DigitalCraftsman\CQRS\RequestValidator\GuardAgainstTokenInHeaderRequestValidator;
use DigitalCraftsman\CQRS\ResponseConstructor\EmptyJsonResponseConstructor;
use DigitalCraftsman\CQRS\Test\Application\AddActionIdRequestDataTransformer;
use DigitalCraftsman\CQRS\Test\Application\Authentication\UserIdValidator;
use DigitalCraftsman\CQRS\Test\Application\ConnectionTransactionWrapper;
use DigitalCraftsman\CQRS\Test\Application\SilentExceptionWrapper;
use DigitalCraftsman\CQRS\Test\Domain\Tasks\ReadSide\GetTasks\Exception\TasksNotAccessible;
use DigitalCraftsman\CQRS\Test\Domain\Tasks\WriteSide\CreateTask\CreateTaskCommand;
use DigitalCraftsman\CQRS\Test\Domain\Tasks\WriteSide\CreateTask\CreateTaskCommandHandler;
use DigitalCraftsman\CQRS\Test\Domain\Tasks\WriteSide\CreateTask\CreateTaskDTOConstructor;
use DigitalCraftsman\CQRS\Test\Domain\Tasks\WriteSide\CreateTask\CreateTaskRequestDecoder;
use DigitalCraftsman\CQRS\Test\Domain\Tasks\WriteSide\MarkTaskAsAccepted\Exception\TaskAlreadyAccepted;
use DigitalCraftsman\CQRS\ValueObject\Exception\InvalidClassInRoutePayload;
use DigitalCraftsman\CQRS\ValueObject\Exception\InvalidParametersInRoutePayload;
use PHPUnit\Framework\TestCase;

/** @coversDefaultClass \DigitalCraftsman\CQRS\ValueObject\RoutePayload */
final class RoutePayloadTest extends TestCase
{
    // -- Construction

    /**
     * @test
     *
     * @covers ::generate
     * @covers ::__construct
     * @covers ::validateDTOClass
     * @covers ::validateHandlerClass
     * @covers ::validateRequestValidatorClasses
     * @covers ::validateRequestDecoderClass
     * @covers ::validateRequestDataTransformerClasses
     * @covers ::validateDTOConstructorClass
     * @covers ::validateDTOValidatorClasses
     * @covers ::validateHandlerWrapperClasses
     * @covers ::validateResponseConstructorClass
     * @covers ::toPayload
     */
    public function generate_works(): void
    {
        // -- Arrange & Act
        $payload = RoutePayload::generate(
            dtoClass: CreateTaskCommand::class,
            handlerClass: CreateTaskCommandHandler::class,
            requestValidatorClasses: [
                GuardAgainstTokenInHeaderRequestValidator::class => null,
            ],
            requestValidatorClassesToMergeWithDefault: null,
            requestDecoderClass: CreateTaskRequestDecoder::class,
            requestDataTransformerClasses: [
                AddActionIdRequestDataTransformer::class => null,
            ],
            requestDataTransformerClassesToMergeWithDefault: null,
            dtoConstructorClass: CreateTaskDTOConstructor::class,
            dtoValidatorClasses: [
                UserIdValidator::class => null,
            ],
            dtoValidatorClassesToMergeWithDefault: null,
            handlerWrapperClasses: [
                SilentExceptionWrapper::class => [
                    TaskAlreadyAccepted::class,
                ],
            ],
            handlerWrapperClassesToMergeWithDefault: null,
            responseConstructorClass: EmptyJsonResponseConstructor::class,
        );

        // -- Assert
        self::assertEquals([
            'dtoClass' => CreateTaskCommand::class,
            'handlerClass' => CreateTaskCommandHandler::class,
            'requestValidatorClasses' => [
                GuardAgainstTokenInHeaderRequestValidator::class => null,
            ],
            'requestValidatorClassesToMergeWithDefault' => null,
            'requestDecoderClass' => CreateTaskRequestDecoder::class,
            'requestDataTransformerClasses' => [
                AddActionIdRequestDataTransformer::class => null,
            ],
            'requestDataTransformerClassesToMergeWithDefault' => null,
            'dtoConstructorClass' => CreateTaskDTOConstructor::class,
            'dtoValidatorClasses' => [
                UserIdValidator::class => null,
            ],
            'dtoValidatorClassesToMergeWithDefault' => null,
            'handlerWrapperClasses' => [
                SilentExceptionWrapper::class => [
                    TaskAlreadyAccepted::class,
                ],
            ],
            'handlerWrapperClassesToMergeWithDefault' => null,
            'responseConstructorClass' => EmptyJsonResponseConstructor::class,
        ], $payload);
    }

    /**
     * @test
     *
     * @covers ::fromPayload
     */
    public function from_payload_works(): void
    {
        // -- Arrange
        $payload = [
            'dtoClass' => CreateTaskCommand::class,
            'handlerClass' => CreateTaskCommandHandler::class,
            'requestValidatorClasses' => [
                GuardAgainstTokenInHeaderRequestValidator::class => null,
            ],
            'requestValidatorClassesToMergeWithDefault' => null,
            'requestDecoderClass' => CreateTaskRequestDecoder::class,
            'requestDataTransformerClasses' => [
                AddActionIdRequestDataTransformer::class => null,
            ],
            'requestDataTransformerClassesToMergeWithDefault' => null,
            'dtoConstructorClass' => CreateTaskDTOConstructor::class,
            'dtoValidatorClasses' => [
                UserIdValidator::class => null,
            ],
            'dtoValidatorClassesToMergeWithDefault' => null,
            'handlerWrapperClasses' => [
                SilentExceptionWrapper::class => [
                    TaskAlreadyAccepted::class,
                ],
            ],
            'handlerWrapperClassesToMergeWithDefault' => null,
            'responseConstructorClass' => EmptyJsonResponseConstructor::class,
        ];

        // -- Act
        $routePayload = RoutePayload::fromPayload($payload);

        // -- Assert
        self::assertSame(CreateTaskCommand::class, $routePayload->dtoClass);
        self::assertSame(CreateTaskCommandHandler::class, $routePayload->handlerClass);
        self::assertSame([
            GuardAgainstTokenInHeaderRequestValidator::class => null,
        ], $routePayload->requestValidatorClasses);
        self::assertNull($routePayload->requestValidatorClassesToMergeWithDefault);
        self::assertSame(CreateTaskRequestDecoder::class, $routePayload->requestDecoderClass);
        self::assertSame([
            AddActionIdRequestDataTransformer::class => null,
        ], $routePayload->requestDataTransformerClasses);
        self::assertNull($routePayload->requestDataTransformerClassesToMergeWithDefault);
        self::assertSame(CreateTaskDTOConstructor::class, $routePayload->dtoConstructorClass);
        self::assertSame([
            UserIdValidator::class => null,
        ], $routePayload->dtoValidatorClasses);
        self::assertNull($routePayload->dtoValidatorClassesToMergeWithDefault);
        self::assertSame([
            SilentExceptionWrapper::class => [
                TaskAlreadyAccepted::class,
            ],
        ], $routePayload->handlerWrapperClasses);
        self::assertNull($routePayload->handlerWrapperClassesToMergeWithDefault);
        self::assertSame(EmptyJsonResponseConstructor::class, $routePayload->responseConstructorClass);
    }

    // -- Validate DTO class

    /**
     * @test
     *
     * @covers ::validateDTOClass
     */
    public function validate_dto_class_fails_when_class_does_not_exist(): void
    {
        // -- Assert
        $this->expectException(InvalidClassInRoutePayload::class);

        // -- Act
        RoutePayload::validateDTOClass('App\DoesNotExist');
    }

    // -- Validate handler class

    /**
     * @test
     *
     * @covers ::validateHandlerClass
     */
    public function validate_handler_class_fails_when_class_does_not_exist(): void
    {
        // -- Assert
        $this->expectException(InvalidClassInRoutePayload::class);

        // -- Act
        RoutePayload::validateHandlerClass('App\DoesNotExist');
    }

    // -- Validate request validator classes

    /**
     * @test
     *
     * @covers ::validateRequestValidatorClasses
     */
    public function validate_request_validator_classes_fails_when_class_is_not_provided(): void
    {
        // -- Assert
        $this->expectException(InvalidClassInRoutePayload::class);

        // -- Act
        /** @psalm-suppress InvalidScalarArgument */
        RoutePayload::validateRequestValidatorClasses([
            'App\DoesNotExist',
        ], null);
    }

    /**
     * @test
     *
     * @covers ::validateRequestValidatorClasses
     */
    public function validate_request_validator_classes_fails_when_class_does_not_exist(): void
    {
        // -- Assert
        $this->expectException(InvalidClassInRoutePayload::class);

        // -- Act
        /** @psalm-suppress ArgumentTypeCoercion */
        RoutePayload::validateRequestValidatorClasses([
            'App\DoesNotExist' => null,
        ], null);
    }

    /**
     * @test
     *
     * @covers ::validateRequestValidatorClasses
     */
    public function validate_request_validator_classes_fails_when_parameters_are_not_valid(): void
    {
        // -- Assert
        $this->expectException(InvalidParametersInRoutePayload::class);

        // -- Act
        RoutePayload::validateRequestValidatorClasses([
            GuardAgainstTokenInHeaderRequestValidator::class => 'invalid-parameter',
        ], null);
    }

    // -- Validate request data class

    /**
     * @test
     *
     * @covers ::validateRequestDecoderClass
     */
    public function validate_request_decoder_class_fails_when_class_does_not_exist(): void
    {
        // -- Assert
        $this->expectException(InvalidClassInRoutePayload::class);

        // -- Act
        RoutePayload::validateRequestDecoderClass('App\DoesNotExist');
    }

    // -- Validate request data transformer classes

    /**
     * @test
     *
     * @covers ::validateRequestDataTransformerClasses
     */
    public function validate_request_data_transformer_classes_fails_when_class_is_not_provided(): void
    {
        // -- Assert
        $this->expectException(InvalidClassInRoutePayload::class);

        // -- Act
        /** @psalm-suppress InvalidScalarArgument */
        RoutePayload::validateRequestDataTransformerClasses([
            'App\DoesNotExist',
        ], null);
    }

    /**
     * @test
     *
     * @covers ::validateRequestDataTransformerClasses
     */
    public function validate_request_data_transformer_classes_fails_when_class_does_not_exist(): void
    {
        // -- Assert
        $this->expectException(InvalidClassInRoutePayload::class);

        // -- Act
        /** @psalm-suppress ArgumentTypeCoercion */
        RoutePayload::validateRequestDataTransformerClasses([
            'App\DoesNotExist' => null,
        ], null);
    }

    /**
     * @test
     *
     * @covers ::validateRequestDataTransformerClasses
     */
    public function validate_request_data_transformer_classes_fails_when_parameters_are_not_valid(): void
    {
        // -- Assert
        $this->expectException(InvalidParametersInRoutePayload::class);

        // -- Act
        RoutePayload::validateRequestDataTransformerClasses([
            AddActionIdRequestDataTransformer::class => 'invalid-parameter',
        ], null);
    }

    // -- Validate DTO constructor class

    /**
     * @test
     *
     * @covers ::validateDTOConstructorClass
     */
    public function validate_dto_constructor_class_fails_when_class_does_not_exist(): void
    {
        // -- Assert
        $this->expectException(InvalidClassInRoutePayload::class);

        // -- Act
        RoutePayload::validateDTOConstructorClass('App\DoesNotExist');
    }

    // -- Validate DTO validator classes

    /**
     * @test
     *
     * @covers ::validateDTOValidatorClasses
     */
    public function validate_dto_validator_classes_fails_when_class_is_not_provided(): void
    {
        // -- Assert
        $this->expectException(InvalidClassInRoutePayload::class);

        // -- Act
        /** @psalm-suppress InvalidScalarArgument */
        RoutePayload::validateDTOValidatorClasses([
            'App\DoesNotExist',
        ], null);
    }

    /**
     * @test
     *
     * @covers ::validateDTOValidatorClasses
     */
    public function validate_dto_validator_classes_fails_when_class_does_not_exist(): void
    {
        // -- Assert
        $this->expectException(InvalidClassInRoutePayload::class);

        // -- Act
        /** @psalm-suppress ArgumentTypeCoercion */
        RoutePayload::validateDTOValidatorClasses([
            'App\DoesNotExist' => null,
        ], null);
    }

    /**
     * @test
     *
     * @covers ::validateDTOValidatorClasses
     */
    public function validate_dto_validator_classes_fails_when_parameters_are_not_valid(): void
    {
        // -- Assert
        $this->expectException(InvalidParametersInRoutePayload::class);

        // -- Act
        RoutePayload::validateDTOValidatorClasses([
            UserIdValidator::class => 'invalid-parameter',
        ], null);
    }

    // -- Validate handler wrapper classes

    /**
     * @test
     *
     * @covers ::validateHandlerWrapperClasses
     */
    public function validate_handler_wrapper_classes_fails_when_class_is_not_provided(): void
    {
        // -- Assert
        $this->expectException(InvalidClassInRoutePayload::class);

        // -- Act
        /** @psalm-suppress InvalidScalarArgument */
        RoutePayload::validateHandlerWrapperClasses([
            'App\DoesNotExist',
        ], null);
    }

    /**
     * @test
     *
     * @covers ::validateHandlerWrapperClasses
     */
    public function validate_handler_wrapper_classes_fails_when_class_does_not_exist(): void
    {
        // -- Assert
        $this->expectException(InvalidClassInRoutePayload::class);

        // -- Act
        /** @psalm-suppress ArgumentTypeCoercion */
        RoutePayload::validateHandlerWrapperClasses([
            'App\DoesNotExist' => null,
        ], null);
    }

    /**
     * @test
     *
     * @covers ::validateHandlerWrapperClasses
     */
    public function validate_handler_wrapper_classes_fails_when_parameters_are_not_valid(): void
    {
        // -- Assert
        $this->expectException(InvalidParametersInRoutePayload::class);

        // -- Act
        RoutePayload::validateHandlerWrapperClasses([
            SilentExceptionWrapper::class => [
                UserIdValidator::class,
            ],
        ], null);
    }

    // -- Validate response constructor class

    /**
     * @test
     *
     * @covers ::validateResponseConstructorClass
     */
    public function validate_response_constructor_class_fails_when_class_does_not_exist(): void
    {
        // -- Assert
        $this->expectException(InvalidClassInRoutePayload::class);

        // -- Act
        RoutePayload::validateResponseConstructorClass('App\DoesNotExist');
    }

    // -- Merge classes from route with defaults

    /**
     * @test
     *
     * @covers ::mergeClassesFromRouteWithDefaults
     */
    public function merge_classes_from_route_with_defaults_works_with_overwrite(): void
    {
        // -- Arrange
        $classesFromDefault = [
            ConnectionTransactionWrapper::class => null,
        ];
        $classesFromRoute = [
            SilentExceptionWrapper::class => [
                TaskAlreadyAccepted::class,
            ],
        ];
        $classesFromRouteToMergeWithDefault = null;

        // -- Act
        $relevantClasses = RoutePayload::mergeClassesFromRouteWithDefaults(
            $classesFromRoute,
            $classesFromRouteToMergeWithDefault,
            $classesFromDefault,
        );

        // -- Assert
        self::assertSame($classesFromRoute, $relevantClasses);
    }

    /**
     * @test
     *
     * @covers ::mergeClassesFromRouteWithDefaults
     */
    public function merge_classes_from_route_with_defaults_works_with_merge_into_defaults(): void
    {
        // -- Arrange
        $classesFromDefault = [
            ConnectionTransactionWrapper::class => null,
        ];
        $classesFromRoute = null;
        $classesFromRouteToMergeWithDefault = [
            SilentExceptionWrapper::class => [
                TaskAlreadyAccepted::class,
            ],
        ];

        // -- Act
        $relevantClasses = RoutePayload::mergeClassesFromRouteWithDefaults(
            $classesFromRoute,
            $classesFromRouteToMergeWithDefault,
            $classesFromDefault,
        );

        // -- Assert
        self::assertSame([
            ConnectionTransactionWrapper::class => null,
            SilentExceptionWrapper::class => [
                TaskAlreadyAccepted::class,
            ],
        ], $relevantClasses);
    }

    /**
     * @test
     *
     * @covers ::mergeClassesFromRouteWithDefaults
     */
    public function merge_classes_from_route_with_defaults_works_with_merge_into_defaults_and_parameters_are_used_from_route(): void
    {
        // -- Arrange
        $classesFromDefault = [
            ConnectionTransactionWrapper::class => null,
            SilentExceptionWrapper::class => [
                TasksNotAccessible::class,
            ],
        ];
        $classesFromRoute = null;
        $classesFromRouteToMergeWithDefault = [
            SilentExceptionWrapper::class => [
                TaskAlreadyAccepted::class,
            ],
        ];

        // -- Act
        $relevantClasses = RoutePayload::mergeClassesFromRouteWithDefaults(
            $classesFromRoute,
            $classesFromRouteToMergeWithDefault,
            $classesFromDefault,
        );

        // -- Assert
        self::assertSame([
            ConnectionTransactionWrapper::class => null,
            SilentExceptionWrapper::class => [
                TaskAlreadyAccepted::class,
            ],
        ], $relevantClasses);
    }

    /**
     * @test
     *
     * @covers ::mergeClassesFromRouteWithDefaults
     */
    public function merge_classes_from_route_with_defaults_works_without_classes_from_route(): void
    {
        // -- Arrange
        $classesFromDefault = [
            ConnectionTransactionWrapper::class => null,
        ];
        $classesFromRoute = null;
        $classesFromRouteToMergeWithDefault = null;

        // -- Act
        $relevantClasses = RoutePayload::mergeClassesFromRouteWithDefaults(
            $classesFromRoute,
            $classesFromRouteToMergeWithDefault,
            $classesFromDefault,
        );

        // -- Assert

        // Uses default
        self::assertSame($classesFromDefault, $relevantClasses);
    }

    /**
     * @test
     *
     * @covers ::mergeClassesFromRouteWithDefaults
     */
    public function merge_classes_from_route_with_defaults_works_with_empty_list_from_route(): void
    {
        // -- Arrange
        $classesFromDefault = [
            ConnectionTransactionWrapper::class => null,
        ];
        $classesFromRoute = [];
        $classesFromRouteToMergeWithDefault = null;

        // -- Act
        $relevantClasses = RoutePayload::mergeClassesFromRouteWithDefaults(
            $classesFromRoute,
            $classesFromRouteToMergeWithDefault,
            $classesFromDefault,
        );

        // -- Assert

        // Default is removed
        self::assertSame($classesFromRoute, $relevantClasses);
    }

    /**
     * @test
     *
     * @covers ::mergeClassesFromRouteWithDefaults
     */
    public function merge_classes_from_route_with_defaults_works_without_values(): void
    {
        // -- Arrange && Act
        $relevantClasses = RoutePayload::mergeClassesFromRouteWithDefaults(
            null,
            null,
            null,
        );

        // -- Assert
        self::assertSame([], $relevantClasses);
    }
}
