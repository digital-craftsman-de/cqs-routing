<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ValueObject;

use DigitalCraftsman\CQRS\RequestValidator\GuardAgainstFileWithVirusRequestValidator;
use DigitalCraftsman\CQRS\RequestValidator\GuardAgainstTokenInHeaderRequestValidator;
use DigitalCraftsman\CQRS\ResponseConstructor\EmptyJsonResponseConstructor;
use DigitalCraftsman\CQRS\Routing\Exception\ClassIsNetherCommandHandlerNorQueryHandler;
use DigitalCraftsman\CQRS\Routing\Exception\ClassIsNetherCommandNorQuery;
use DigitalCraftsman\CQRS\Routing\Exception\ClassIsNoDTOConstructor;
use DigitalCraftsman\CQRS\Routing\Exception\ClassIsNoDTOValidator;
use DigitalCraftsman\CQRS\Routing\Exception\ClassIsNoHandlerWrapper;
use DigitalCraftsman\CQRS\Routing\Exception\ClassIsNoRequestDataTransformer;
use DigitalCraftsman\CQRS\Routing\Exception\ClassIsNoRequestDecoder;
use DigitalCraftsman\CQRS\Routing\Exception\ClassIsNoRequestValidator;
use DigitalCraftsman\CQRS\Routing\Exception\ClassIsNoResponseConstructor;
use DigitalCraftsman\CQRS\Routing\Exception\InvalidClassInRoutePayload;
use DigitalCraftsman\CQRS\Routing\Exception\InvalidParametersInRoutePayload;
use DigitalCraftsman\CQRS\Routing\Exception\OnlyOverwriteOrMergeCanBeUsedInRoutePayload;
use DigitalCraftsman\CQRS\Routing\RoutePayload;
use DigitalCraftsman\CQRS\Test\Application\AddActionIdRequestDataTransformer;
use DigitalCraftsman\CQRS\Test\Application\Authentication\UserIdValidator;
use DigitalCraftsman\CQRS\Test\Application\ConnectionTransactionWrapper;
use DigitalCraftsman\CQRS\Test\Application\SilentExceptionWrapper;
use DigitalCraftsman\CQRS\Test\Domain\News\WriteSide\CreateNewsArticle\CreateNewsArticleRequestDataTransformer;
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

    /**
     * @test
     *
     * @covers ::validateDTOClass
     */
    public function validate_dto_class_fails_when_class_is_not_a_command_or_a_query(): void
    {
        // -- Assert
        $this->expectException(ClassIsNetherCommandNorQuery::class);

        // -- Act
        RoutePayload::validateDTOClass(UserIdValidator::class);
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

    /**
     * @test
     *
     * @covers ::validateHandlerClass
     */
    public function validate_handler_class_fails_when_class_is_not_a_command_handler_or_a_query_handler(): void
    {
        // -- Assert
        $this->expectException(ClassIsNetherCommandHandlerNorQueryHandler::class);

        // -- Act
        RoutePayload::validateHandlerClass(UserIdValidator::class);
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

    /**
     * @test
     *
     * @covers ::validateRequestValidatorClasses
     */
    public function validate_request_validator_classes_fails_when_overwrite_and_merge_are_defined(): void
    {
        // -- Assert
        $this->expectException(OnlyOverwriteOrMergeCanBeUsedInRoutePayload::class);

        // -- Act
        RoutePayload::validateRequestValidatorClasses([
            GuardAgainstTokenInHeaderRequestValidator::class => null,
        ], [
            GuardAgainstFileWithVirusRequestValidator::class => null,
        ]);
    }

    /**
     * @test
     *
     * @covers ::validateRequestValidatorClasses
     */
    public function validate_request_validator_classes_fails_when_class_in_merge_list_is_no_request_validator(): void
    {
        // -- Assert
        $this->expectException(ClassIsNoRequestValidator::class);

        // -- Act
        /** @psalm-suppress InvalidArgument */
        RoutePayload::validateRequestValidatorClasses(null, [
            UserIdValidator::class => null,
        ]);
    }

    // -- Validate request decoder class

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

    /**
     * @test
     *
     * @covers ::validateRequestDecoderClass
     */
    public function validate_request_decoder_class_fails_when_class_is_no_request_decoder(): void
    {
        // -- Assert
        $this->expectException(ClassIsNoRequestDecoder::class);

        // -- Act
        RoutePayload::validateRequestDecoderClass(UserIdValidator::class);
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

    /**
     * @test
     *
     * @covers ::validateRequestDataTransformerClasses
     */
    public function validate_request_data_transformer_classes_fails_when_overwrite_and_merge_list_are_used(): void
    {
        // -- Assert
        $this->expectException(OnlyOverwriteOrMergeCanBeUsedInRoutePayload::class);

        // -- Act
        RoutePayload::validateRequestDataTransformerClasses([
            AddActionIdRequestDataTransformer::class => null,
        ], [
            CreateNewsArticleRequestDataTransformer::class => null,
        ]);
    }

    /**
     * @test
     *
     * @covers ::validateRequestDataTransformerClasses
     */
    public function validate_request_data_transformer_classes_fails_when_class_in_merge_list_is_no_request_data_transformer(): void
    {
        // -- Assert
        $this->expectException(ClassIsNoRequestDataTransformer::class);

        // -- Act
        /** @psalm-suppress InvalidArgument */
        RoutePayload::validateRequestDataTransformerClasses(null, [
            UserIdValidator::class => null,
        ]);
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

    /**
     * @test
     *
     * @covers ::validateDTOConstructorClass
     */
    public function validate_dto_constructor_class_fails_when_class_is_no_dto_constructor(): void
    {
        // -- Assert
        $this->expectException(ClassIsNoDTOConstructor::class);

        // -- Act
        RoutePayload::validateDTOConstructorClass(UserIdValidator::class);
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

    /**
     * @test
     *
     * @covers ::validateDTOValidatorClasses
     */
    public function validate_dto_validator_classes_fails_when_overwrite_and_merge_list_are_used(): void
    {
        // -- Assert
        $this->expectException(OnlyOverwriteOrMergeCanBeUsedInRoutePayload::class);

        // -- Act
        RoutePayload::validateDTOValidatorClasses([
            UserIdValidator::class => null,
        ], [
            UserIdValidator::class => null,
        ]);
    }

    /**
     * @test
     *
     * @covers ::validateDTOValidatorClasses
     */
    public function validate_dto_validator_classes_fails_when_class_in_merge_list_is_no_dto_validator(): void
    {
        // -- Assert
        $this->expectException(ClassIsNoDTOValidator::class);

        // -- Act
        /** @psalm-suppress InvalidArgument */
        RoutePayload::validateDTOValidatorClasses(null, [
            AddActionIdRequestDataTransformer::class => null,
        ]);
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

    /**
     * @test
     *
     * @covers ::validateHandlerWrapperClasses
     */
    public function validate_handler_wrapper_classes_fails_when_overwrite_and_merge_list_are_used(): void
    {
        // -- Assert
        $this->expectException(OnlyOverwriteOrMergeCanBeUsedInRoutePayload::class);

        // -- Act
        RoutePayload::validateHandlerWrapperClasses([
            SilentExceptionWrapper::class => [
                UserIdValidator::class,
            ],
        ], [
            ConnectionTransactionWrapper::class => null,
        ]);
    }

    /**
     * @test
     *
     * @covers ::validateHandlerWrapperClasses
     */
    public function validate_handler_wrapper_classes_fails_when_class_in_merge_list_is_no_handler_wrapper(): void
    {
        // -- Assert
        $this->expectException(ClassIsNoHandlerWrapper::class);

        // -- Act
        /** @psalm-suppress InvalidArgument */
        RoutePayload::validateHandlerWrapperClasses(null, [
            UserIdValidator::class => null,
        ]);
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

    /**
     * @test
     *
     * @covers ::validateResponseConstructorClass
     */
    public function validate_response_constructor_class_fails_when_class_is_no_response_constructor(): void
    {
        // -- Assert
        $this->expectException(ClassIsNoResponseConstructor::class);

        // -- Act
        RoutePayload::validateResponseConstructorClass(UserIdValidator::class);
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
