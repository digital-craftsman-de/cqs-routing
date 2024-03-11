<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Routing;

use DigitalCraftsman\CQRS\HandlerWrapper\SilentExceptionWrapper;
use DigitalCraftsman\CQRS\RequestValidator\GuardAgainstTokenInHeaderRequestValidator;
use DigitalCraftsman\CQRS\ResponseConstructor\EmptyJsonResponseConstructor;
use DigitalCraftsman\CQRS\Test\Application\AddActionIdRequestDataTransformer;
use DigitalCraftsman\CQRS\Test\Application\Authentication\UserIdValidator;
use DigitalCraftsman\CQRS\Test\Application\ConnectionTransactionWrapper;
use DigitalCraftsman\CQRS\Test\Domain\Tasks\ReadSide\GetTasks\Exception\TasksNotAccessible;
use DigitalCraftsman\CQRS\Test\Domain\Tasks\WriteSide\CreateTask\CreateTaskCommand;
use DigitalCraftsman\CQRS\Test\Domain\Tasks\WriteSide\CreateTask\CreateTaskCommandHandler;
use DigitalCraftsman\CQRS\Test\Domain\Tasks\WriteSide\CreateTask\CreateTaskDTOConstructor;
use DigitalCraftsman\CQRS\Test\Domain\Tasks\WriteSide\CreateTask\CreateTaskRequestDecoder;
use DigitalCraftsman\CQRS\Test\Domain\Tasks\WriteSide\MarkTaskAsAccepted\Exception\TaskAlreadyAccepted;
use PHPUnit\Framework\TestCase;

/** @coversDefaultClass \DigitalCraftsman\CQRS\Routing\RoutePayload */
final class RoutePayloadTest extends TestCase
{
    // -- Construction

    /**
     * @test
     *
     * @covers ::generatePayload
     * @covers ::__construct
     * @covers ::toPayload
     */
    public function generate_payload_works(): void
    {
        // -- Arrange & Act
        $payload = RoutePayload::generatePayload(
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

    // -- Merge classes from route with defaults

    /**
     * @test
     *
     * @covers ::self::mergeHandlerWrapperClassesFromRouteWithDefaults()
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
        $relevantClasses = RoutePayload::mergeHandlerWrapperClassesFromRouteWithDefaults(
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
     * @covers ::self::mergeHandlerWrapperClassesFromRouteWithDefaults()
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
        $relevantClasses = RoutePayload::mergeHandlerWrapperClassesFromRouteWithDefaults(
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
     * @covers ::self::mergeHandlerWrapperClassesFromRouteWithDefaults()
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
        $relevantClasses = RoutePayload::mergeHandlerWrapperClassesFromRouteWithDefaults(
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
     * @covers ::self::mergeHandlerWrapperClassesFromRouteWithDefaults()
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
        $relevantClasses = RoutePayload::mergeHandlerWrapperClassesFromRouteWithDefaults(
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
     * @covers ::self::mergeHandlerWrapperClassesFromRouteWithDefaults()
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
        $relevantClasses = RoutePayload::mergeHandlerWrapperClassesFromRouteWithDefaults(
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
     * @covers ::self::mergeHandlerWrapperClassesFromRouteWithDefaults()
     */
    public function merge_classes_from_route_with_defaults_works_without_values(): void
    {
        // -- Arrange && Act
        $relevantClasses = RoutePayload::mergeHandlerWrapperClassesFromRouteWithDefaults(
            null,
            null,
            null,
        );

        // -- Assert
        self::assertSame([], $relevantClasses);
    }
}
