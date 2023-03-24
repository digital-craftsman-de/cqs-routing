<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Routing;

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
use DigitalCraftsman\CQRS\Test\Application\SilentExceptionWrapper;
use DigitalCraftsman\CQRS\Test\Domain\News\WriteSide\CreateNewsArticle\CreateNewsArticleRequestDataTransformer;
use PHPUnit\Framework\TestCase;

/** @coversDefaultClass \DigitalCraftsman\CQRS\Routing\RouteParameters */
final class RouteParametersTest extends TestCase
{
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
        RouteParameters::validateDTOClass('App\DoesNotExist');
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
        RouteParameters::validateDTOClass(UserIdValidator::class);
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
        RouteParameters::validateHandlerClass('App\DoesNotExist');
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
        RouteParameters::validateHandlerClass(UserIdValidator::class);
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
        RouteParameters::validateRequestValidatorClasses([
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
        RouteParameters::validateRequestValidatorClasses([
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
        RouteParameters::validateRequestValidatorClasses([
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
        RouteParameters::validateRequestValidatorClasses([
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
        RouteParameters::validateRequestValidatorClasses(null, [
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
        RouteParameters::validateRequestDecoderClass('App\DoesNotExist');
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
        RouteParameters::validateRequestDecoderClass(UserIdValidator::class);
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
        RouteParameters::validateRequestDataTransformerClasses([
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
        RouteParameters::validateRequestDataTransformerClasses([
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
        RouteParameters::validateRequestDataTransformerClasses([
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
        RouteParameters::validateRequestDataTransformerClasses([
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
        RouteParameters::validateRequestDataTransformerClasses(null, [
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
        RouteParameters::validateDTOConstructorClass('App\DoesNotExist');
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
        RouteParameters::validateDTOConstructorClass(UserIdValidator::class);
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
        RouteParameters::validateDTOValidatorClasses([
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
        RouteParameters::validateDTOValidatorClasses([
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
        RouteParameters::validateDTOValidatorClasses([
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
        RouteParameters::validateDTOValidatorClasses([
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
        RouteParameters::validateDTOValidatorClasses(null, [
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
        RouteParameters::validateHandlerWrapperClasses([
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
        RouteParameters::validateHandlerWrapperClasses([
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
        RouteParameters::validateHandlerWrapperClasses([
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
        RouteParameters::validateHandlerWrapperClasses([
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
        RouteParameters::validateHandlerWrapperClasses(null, [
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
        RouteParameters::validateResponseConstructorClass('App\DoesNotExist');
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
        RouteParameters::validateResponseConstructorClass(UserIdValidator::class);
    }
}
