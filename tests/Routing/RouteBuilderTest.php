<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Routing;

use DigitalCraftsman\CQRS\Command\Command;
use DigitalCraftsman\CQRS\HandlerWrapper\SilentExceptionWrapper;
use DigitalCraftsman\CQRS\RequestValidator\GuardAgainstFileWithVirusRequestValidator;
use DigitalCraftsman\CQRS\RequestValidator\GuardAgainstTokenInHeaderRequestValidator;
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
use DigitalCraftsman\CQRS\Test\Application\AddActionIdRequestDataTransformer;
use DigitalCraftsman\CQRS\Test\Application\Authentication\UserIdValidator;
use DigitalCraftsman\CQRS\Test\Application\ConnectionTransactionWrapper;
use DigitalCraftsman\CQRS\Test\Domain\News\WriteSide\CreateNewsArticle\CreateNewsArticleRequestDataTransformer;
use PHPUnit\Framework\TestCase;

/** @coversDefaultClass \DigitalCraftsman\CQRS\Routing\RouteBuilder */
class RouteBuilderTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::generateNameFromPath
     *
     * @dataProvider dataProvider
     */
    public function generate_name_from_path_works(
        string $expectedResult,
        string $name,
    ): void {
        // -- Act & Assert
        self::assertSame($expectedResult, RouteBuilder::generateNameFromPath($name));
    }

    /**
     * @return array<string, array{
     *   0: string,
     *   1: string,
     * }>
     */
    public function dataProvider(): array
    {
        return [
            'route with slash at the beginning' => [
                'api_tasks_create_task_command',
                '/api/tasks/create-task-command',
            ],
            'route without slash at the beginning' => [
                'api_tasks_create_task_command',
                'api/tasks/create-task-command',
            ],
            'route with parameter' => [
                'api_tasks_get_task_image_query_id',
                '/api/tasks/get-task-image-query/{id}',
            ],
            'route with camlCase parameter' => [
                'api_tasks_get_task_image_query_user_id',
                '/api/tasks/get-task-image-query/{userId}',
            ],
            'route with uppercase parameter' => [
                'api_tasks_get_task_image_query_user_id',
                '/api/tasks/get-task-image-query/{UserId}',
            ],
        ];
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

        // -- Arrange
        /** @var class-string<Command> $classString */
        $classString = 'App\DoesNotExist';

        // -- Act
        RouteBuilder::validateDTOClass($classString);
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
        /** @psalm-suppress InvalidArgument Invalid argument supplied on purpose */
        RouteBuilder::validateDTOClass(UserIdValidator::class);
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
        /** @psalm-suppress ArgumentTypeCoercion Invalid argument supplied on purpose */
        RouteBuilder::validateHandlerClass('App\DoesNotExist');
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
        /** @psalm-suppress InvalidArgument Invalid argument supplied on purpose */
        RouteBuilder::validateHandlerClass(UserIdValidator::class);
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
        /**
         * @psalm-suppress InvalidScalarArgument
         * @psalm-suppress InvalidArgument
         */
        RouteBuilder::validateRequestValidatorClasses([
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
        RouteBuilder::validateRequestValidatorClasses([
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
        RouteBuilder::validateRequestValidatorClasses([
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
        RouteBuilder::validateRequestValidatorClasses([
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
        RouteBuilder::validateRequestValidatorClasses(null, [
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
        /** @psalm-suppress ArgumentTypeCoercion Invalid argument supplied on purpose */
        RouteBuilder::validateRequestDecoderClass('App\DoesNotExist');
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
        /** @psalm-suppress InvalidArgument Invalid argument supplied on purpose */
        RouteBuilder::validateRequestDecoderClass(UserIdValidator::class);
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
        /** @psalm-suppress InvalidArgument */
        RouteBuilder::validateRequestDataTransformerClasses([
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
        RouteBuilder::validateRequestDataTransformerClasses([
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
        RouteBuilder::validateRequestDataTransformerClasses([
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
        RouteBuilder::validateRequestDataTransformerClasses([
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
        RouteBuilder::validateRequestDataTransformerClasses(null, [
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
        /** @psalm-suppress ArgumentTypeCoercion Invalid argument supplied on purpose */
        RouteBuilder::validateDTOConstructorClass('App\DoesNotExist');
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
        /** @psalm-suppress InvalidArgument Invalid argument supplied on purpose */
        RouteBuilder::validateDTOConstructorClass(UserIdValidator::class);
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
        /** @psalm-suppress InvalidArgument */
        RouteBuilder::validateDTOValidatorClasses([
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
        RouteBuilder::validateDTOValidatorClasses([
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
        RouteBuilder::validateDTOValidatorClasses([
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
        RouteBuilder::validateDTOValidatorClasses([
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
        RouteBuilder::validateDTOValidatorClasses(null, [
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
        /** @psalm-suppress InvalidArgument */
        RouteBuilder::validateHandlerWrapperClasses([
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
        RouteBuilder::validateHandlerWrapperClasses([
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
        RouteBuilder::validateHandlerWrapperClasses([
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
        RouteBuilder::validateHandlerWrapperClasses([
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
        RouteBuilder::validateHandlerWrapperClasses(null, [
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
        /** @psalm-suppress ArgumentTypeCoercion Invalid argument supplied on purpose */
        RouteBuilder::validateResponseConstructorClass('App\DoesNotExist');
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
        /** @psalm-suppress InvalidArgument Invalid argument supplied on purpose */
        RouteBuilder::validateResponseConstructorClass(UserIdValidator::class);
    }
}
