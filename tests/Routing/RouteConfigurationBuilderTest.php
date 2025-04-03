<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Routing;

use DigitalCraftsman\CQSRouting\DTOConstructor\SerializerDTOConstructor;
use DigitalCraftsman\CQSRouting\HandlerWrapper\SilentExceptionWrapper;
use DigitalCraftsman\CQSRouting\RequestDecoder\JsonRequestDecoder;
use DigitalCraftsman\CQSRouting\ResponseConstructor\EmptyResponseConstructor;
use DigitalCraftsman\CQSRouting\ResponseConstructor\SerializerJsonResponseConstructor;
use DigitalCraftsman\CQSRouting\Test\Application\AddActionIdRequestDataTransformer;
use DigitalCraftsman\CQSRouting\Test\Application\UserIdValidator;
use DigitalCraftsman\CQSRouting\Test\AppTestCase;
use DigitalCraftsman\CQSRouting\Test\Domain\News\WriteSide\CreateNewsArticle\CreateNewsArticleCommand;
use DigitalCraftsman\CQSRouting\Test\Domain\News\WriteSide\CreateNewsArticle\CreateNewsArticleCommandHandler;
use DigitalCraftsman\CQSRouting\Test\Domain\News\WriteSide\CreateNewsArticle\Exception\NewsArticleAlreadyExists;
use DigitalCraftsman\CQSRouting\Test\Domain\Tasks\ReadSide\GetTasks\GetTasksQuery;
use DigitalCraftsman\CQSRouting\Test\Domain\Tasks\ReadSide\GetTasks\GetTasksQueryHandler;
use DigitalCraftsman\CQSRouting\Test\Domain\Tasks\WriteSide\CreateTask\CreateTaskCommand;
use DigitalCraftsman\CQSRouting\Test\Domain\Tasks\WriteSide\CreateTask\CreateTaskCommandHandler;
use DigitalCraftsman\CQSRouting\Test\Domain\Tasks\WriteSide\CreateTask\CreateTaskDTOConstructor;
use DigitalCraftsman\CQSRouting\Test\Domain\Tasks\WriteSide\CreateTask\CreateTaskRequestDecoder;
use DigitalCraftsman\CQSRouting\Test\RequestValidator\GuardAgainstFileWithVirusRequestValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(RouteConfigurationBuilder::class)]
#[CoversClass(RouteConfiguration::class)]
#[CoversClass(Exception\RequestDecoderOrDefaultRequestDecoderMustBeConfigured::class)]
#[CoversClass(Exception\DTOConstructorOrDefaultDTOConstructorMustBeConfigured::class)]
#[CoversClass(Exception\ResponseConstructorOrDefaultResponseConstructorMustBeConfigured::class)]
final class RouteConfigurationBuilderTest extends AppTestCase
{
    #[Test]
    public function build_works_for_command_without_defaults(): void
    {
        // -- Arrange
        $routeConfigurationBuilder = new RouteConfigurationBuilder(
            defaultRequestValidatorClassesForCommand: null,
            defaultRequestDecoderClassForCommand: null,
            defaultRequestDataTransformerClassesForCommand: null,
            defaultDTOConstructorClassForCommand: null,
            defaultDTOValidatorClassesForCommand: null,
            defaultHandlerWrapperClassesForCommand: null,
            defaultResponseConstructorClassForCommand: null,
        );

        // -- Act
        $routeConfiguration = $routeConfigurationBuilder->buildConfigurationForCommand(
            routePayload: new RoutePayload(
                dtoClass: CreateNewsArticleCommand::class,
                handlerClass: CreateNewsArticleCommandHandler::class,
                requestValidatorClasses: [
                    GuardAgainstFileWithVirusRequestValidator::class => null,
                ],
                requestDecoderClass: JsonRequestDecoder::class,
                requestDataTransformerClasses: [
                    AddActionIdRequestDataTransformer::class => null,
                ],
                dtoConstructorClass: SerializerDTOConstructor::class,
                dtoValidatorClasses: [
                    UserIdValidator::class => null,
                ],
                handlerWrapperClasses: [
                    SilentExceptionWrapper::class => [
                        NewsArticleAlreadyExists::class,
                    ],
                ],
                responseConstructorClass: EmptyResponseConstructor::class,
            ),
        );

        // -- Assert
        self::assertEquals(
            new RouteConfiguration(
                dtoClass: CreateNewsArticleCommand::class,
                handlerClass: CreateNewsArticleCommandHandler::class,
                requestValidatorClasses: [
                    GuardAgainstFileWithVirusRequestValidator::class => null,
                ],
                requestDecoderClass: JsonRequestDecoder::class,
                requestDataTransformerClasses: [
                    AddActionIdRequestDataTransformer::class => null,
                ],
                dtoConstructorClass: SerializerDTOConstructor::class,
                dtoValidatorClasses: [
                    UserIdValidator::class => null,
                ],
                handlerWrapperClasses: [
                    SilentExceptionWrapper::class => [
                        NewsArticleAlreadyExists::class,
                    ],
                ],
                responseConstructorClass: EmptyResponseConstructor::class,
            ),
            $routeConfiguration,
        );
    }

    #[Test]
    public function build_works_for_command_with_defaults(): void
    {
        // -- Arrange
        $routeConfigurationBuilder = new RouteConfigurationBuilder(
            defaultRequestValidatorClassesForCommand: [
                GuardAgainstFileWithVirusRequestValidator::class => null,
            ],
            defaultRequestDecoderClassForCommand: JsonRequestDecoder::class,
            defaultRequestDataTransformerClassesForCommand: [
                AddActionIdRequestDataTransformer::class => null,
            ],
            defaultDTOConstructorClassForCommand: SerializerDTOConstructor::class,
            defaultDTOValidatorClassesForCommand: [
                UserIdValidator::class => null,
            ],
            defaultHandlerWrapperClassesForCommand: [
                SilentExceptionWrapper::class => [
                    NewsArticleAlreadyExists::class,
                ],
            ],
            defaultResponseConstructorClassForCommand: EmptyResponseConstructor::class,
        );

        // -- Act
        $routeConfiguration = $routeConfigurationBuilder->buildConfigurationForCommand(
            routePayload: new RoutePayload(
                dtoClass: CreateNewsArticleCommand::class,
                handlerClass: CreateNewsArticleCommandHandler::class,
            ),
        );

        // -- Assert
        self::assertEquals(
            new RouteConfiguration(
                dtoClass: CreateNewsArticleCommand::class,
                handlerClass: CreateNewsArticleCommandHandler::class,
                requestValidatorClasses: [
                    GuardAgainstFileWithVirusRequestValidator::class => null,
                ],
                requestDecoderClass: JsonRequestDecoder::class,
                requestDataTransformerClasses: [
                    AddActionIdRequestDataTransformer::class => null,
                ],
                dtoConstructorClass: SerializerDTOConstructor::class,
                dtoValidatorClasses: [
                    UserIdValidator::class => null,
                ],
                handlerWrapperClasses: [
                    SilentExceptionWrapper::class => [
                        NewsArticleAlreadyExists::class,
                    ],
                ],
                responseConstructorClass: EmptyResponseConstructor::class,
            ),
            $routeConfiguration,
        );
    }

    #[Test]
    public function build_works_for_command_with_overwritten_defaults(): void
    {
        // -- Arrange
        $routeConfigurationBuilder = new RouteConfigurationBuilder(
            defaultRequestValidatorClassesForCommand: [
                GuardAgainstFileWithVirusRequestValidator::class => null,
            ],
            defaultRequestDecoderClassForCommand: JsonRequestDecoder::class,
            defaultRequestDataTransformerClassesForCommand: [
                AddActionIdRequestDataTransformer::class => null,
            ],
            defaultDTOConstructorClassForCommand: SerializerDTOConstructor::class,
            defaultDTOValidatorClassesForCommand: [
                UserIdValidator::class => null,
            ],
            defaultHandlerWrapperClassesForCommand: [
                SilentExceptionWrapper::class => [
                    NewsArticleAlreadyExists::class,
                ],
            ],
            defaultResponseConstructorClassForCommand: EmptyResponseConstructor::class,
        );

        // -- Act
        $routeConfiguration = $routeConfigurationBuilder->buildConfigurationForCommand(
            routePayload: new RoutePayload(
                dtoClass: CreateTaskCommand::class,
                handlerClass: CreateTaskCommandHandler::class,
                requestValidatorClasses: [],
                requestDecoderClass: CreateTaskRequestDecoder::class,
                requestDataTransformerClasses: [],
                dtoConstructorClass: CreateTaskDTOConstructor::class,
                dtoValidatorClasses: [],
                handlerWrapperClasses: [],
                responseConstructorClass: SerializerJsonResponseConstructor::class,
            ),
        );

        // -- Assert
        self::assertEquals(
            new RouteConfiguration(
                dtoClass: CreateTaskCommand::class,
                handlerClass: CreateTaskCommandHandler::class,
                requestValidatorClasses: [],
                requestDecoderClass: CreateTaskRequestDecoder::class,
                requestDataTransformerClasses: [],
                dtoConstructorClass: CreateTaskDTOConstructor::class,
                dtoValidatorClasses: [],
                handlerWrapperClasses: [],
                responseConstructorClass: SerializerJsonResponseConstructor::class,
            ),
            $routeConfiguration,
        );
    }

    #[Test]
    public function build_works_for_query_without_defaults(): void
    {
        // -- Arrange
        $routeConfigurationBuilder = new RouteConfigurationBuilder(
            defaultRequestValidatorClassesForQuery: null,
            defaultRequestDecoderClassForQuery: null,
            defaultRequestDataTransformerClassesForQuery: null,
            defaultDTOConstructorClassForQuery: null,
            defaultDTOValidatorClassesForQuery: null,
            defaultHandlerWrapperClassesForQuery: null,
            defaultResponseConstructorClassForQuery: null,
        );

        // -- Act
        $routeConfiguration = $routeConfigurationBuilder->buildConfigurationForQuery(
            routePayload: new RoutePayload(
                dtoClass: GetTasksQuery::class,
                handlerClass: GetTasksQueryHandler::class,
                requestValidatorClasses: [
                    GuardAgainstFileWithVirusRequestValidator::class => null,
                ],
                requestDecoderClass: JsonRequestDecoder::class,
                requestDataTransformerClasses: [
                    AddActionIdRequestDataTransformer::class => null,
                ],
                dtoConstructorClass: SerializerDTOConstructor::class,
                dtoValidatorClasses: [
                    UserIdValidator::class => null,
                ],
                handlerWrapperClasses: [
                    SilentExceptionWrapper::class => [
                        NewsArticleAlreadyExists::class,
                    ],
                ],
                responseConstructorClass: SerializerJsonResponseConstructor::class,
            ),
        );

        // -- Assert
        self::assertEquals(
            new RouteConfiguration(
                dtoClass: GetTasksQuery::class,
                handlerClass: GetTasksQueryHandler::class,
                requestValidatorClasses: [
                    GuardAgainstFileWithVirusRequestValidator::class => null,
                ],
                requestDecoderClass: JsonRequestDecoder::class,
                requestDataTransformerClasses: [
                    AddActionIdRequestDataTransformer::class => null,
                ],
                dtoConstructorClass: SerializerDTOConstructor::class,
                dtoValidatorClasses: [
                    UserIdValidator::class => null,
                ],
                handlerWrapperClasses: [
                    SilentExceptionWrapper::class => [
                        NewsArticleAlreadyExists::class,
                    ],
                ],
                responseConstructorClass: SerializerJsonResponseConstructor::class,
            ),
            $routeConfiguration,
        );
    }

    #[Test]
    public function build_works_for_query_with_defaults(): void
    {
        // -- Arrange
        $routeConfigurationBuilder = new RouteConfigurationBuilder(
            defaultRequestValidatorClassesForQuery: [
                GuardAgainstFileWithVirusRequestValidator::class => null,
            ],
            defaultRequestDecoderClassForQuery: JsonRequestDecoder::class,
            defaultRequestDataTransformerClassesForQuery: [
                AddActionIdRequestDataTransformer::class => null,
            ],
            defaultDTOConstructorClassForQuery: SerializerDTOConstructor::class,
            defaultDTOValidatorClassesForQuery: [
                UserIdValidator::class => null,
            ],
            defaultHandlerWrapperClassesForQuery: [
                SilentExceptionWrapper::class => [
                    NewsArticleAlreadyExists::class,
                ],
            ],
            defaultResponseConstructorClassForQuery: SerializerJsonResponseConstructor::class,
        );

        // -- Act
        $routeConfiguration = $routeConfigurationBuilder->buildConfigurationForQuery(
            routePayload: new RoutePayload(
                dtoClass: GetTasksQuery::class,
                handlerClass: GetTasksQueryHandler::class,
            ),
        );

        // -- Assert
        self::assertEquals(
            new RouteConfiguration(
                dtoClass: GetTasksQuery::class,
                handlerClass: GetTasksQueryHandler::class,
                requestValidatorClasses: [
                    GuardAgainstFileWithVirusRequestValidator::class => null,
                ],
                requestDecoderClass: JsonRequestDecoder::class,
                requestDataTransformerClasses: [
                    AddActionIdRequestDataTransformer::class => null,
                ],
                dtoConstructorClass: SerializerDTOConstructor::class,
                dtoValidatorClasses: [
                    UserIdValidator::class => null,
                ],
                handlerWrapperClasses: [
                    SilentExceptionWrapper::class => [
                        NewsArticleAlreadyExists::class,
                    ],
                ],
                responseConstructorClass: SerializerJsonResponseConstructor::class,
            ),
            $routeConfiguration,
        );
    }

    #[Test]
    public function build_fails_when_no_request_decoder_class_and_default_request_decoder_class_is_defined(): void
    {
        // -- Assert
        $this->expectException(Exception\RequestDecoderOrDefaultRequestDecoderMustBeConfigured::class);

        // -- Arrange
        $routeConfigurationBuilder = new RouteConfigurationBuilder(
            defaultRequestDecoderClassForCommand: null,
        );

        // -- Act
        $routeConfigurationBuilder->buildConfigurationForCommand(
            routePayload: new RoutePayload(
                dtoClass: CreateNewsArticleCommand::class,
                handlerClass: CreateNewsArticleCommandHandler::class,
                requestDecoderClass: null,
            ),
        );
    }

    #[Test]
    public function get_dto_constructor_fails_when_no_dto_constructor_class_and_default_dto_constructor_class_is_defined(): void
    {
        // -- Assert
        $this->expectException(Exception\DTOConstructorOrDefaultDTOConstructorMustBeConfigured::class);

        // -- Arrange
        $routeConfigurationBuilder = new RouteConfigurationBuilder(
            defaultRequestDecoderClassForCommand: JsonRequestDecoder::class,
            defaultDTOConstructorClassForCommand: null,
        );

        // -- Act
        $routeConfigurationBuilder->buildConfigurationForCommand(
            routePayload: new RoutePayload(
                dtoClass: CreateNewsArticleCommand::class,
                handlerClass: CreateNewsArticleCommandHandler::class,
                dtoConstructorClass: null,
            ),
        );
    }

    #[Test]
    public function get_response_constructor_fails_when_no_response_constructor_class_and_default_response_constructor_class_is_defined(): void
    {
        // -- Assert
        $this->expectException(Exception\ResponseConstructorOrDefaultResponseConstructorMustBeConfigured::class);

        // -- Arrange
        $routeConfigurationBuilder = new RouteConfigurationBuilder(
            defaultRequestDecoderClassForCommand: JsonRequestDecoder::class,
            defaultDTOConstructorClassForCommand: SerializerDTOConstructor::class,
            defaultResponseConstructorClassForCommand: null,
        );

        // -- Act
        $routeConfigurationBuilder->buildConfigurationForCommand(
            routePayload: new RoutePayload(
                dtoClass: CreateNewsArticleCommand::class,
                handlerClass: CreateNewsArticleCommandHandler::class,
                responseConstructorClass: null,
            ),
        );
    }
}
