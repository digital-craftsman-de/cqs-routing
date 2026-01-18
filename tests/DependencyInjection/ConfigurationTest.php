<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\DependencyInjection;

use DigitalCraftsman\CQSRouting\DTOConstructor\SerializerDTOConstructor;
use DigitalCraftsman\CQSRouting\HandlerWrapper\SilentExceptionWrapper;
use DigitalCraftsman\CQSRouting\RequestDecoder\JsonRequestDecoder;
use DigitalCraftsman\CQSRouting\ResponseConstructor\EmptyJsonResponseConstructor;
use DigitalCraftsman\CQSRouting\ResponseConstructor\SerializerJsonResponseConstructor;
use DigitalCraftsman\CQSRouting\Test\Application\Authentication\UserIdValidator;
use DigitalCraftsman\CQSRouting\Test\Application\ConnectionTransactionWrapper;
use DigitalCraftsman\CQSRouting\Test\Domain\News\WriteSide\CreateNewsArticle\Exception\NewsArticleAlreadyExists;
use DigitalCraftsman\CQSRouting\Test\Domain\Tasks\ReadSide\GetTasks\Exception\TasksNotAccessible;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

/** @coversDefaultClass \DigitalCraftsman\CQSRouting\DependencyInjection\Configuration */
final class ConfigurationTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::getConfigTreeBuilder
     */
    public function configuration_can_be_processed(): void
    {
        // -- Arrange
        $processor = new Processor();
        $configuration = new Configuration();
        $configurationData = [
            'cqs_routing' => [
                'command' => [
                    'default_request_decoder_class' => JsonRequestDecoder::class,
                    'default_dto_constructor_class' => SerializerDTOConstructor::class,
                    'default_dto_validator_classes' => [
                        UserIdValidator::class => null,
                    ],
                    'default_handler_wrapper_classes' => [
                        ConnectionTransactionWrapper::class => null,
                        SilentExceptionWrapper::class => [
                            NewsArticleAlreadyExists::class,
                        ],
                    ],
                    'default_response_constructor_class' => EmptyJsonResponseConstructor::class,
                ],
                'query' => [
                    'default_request_decoder_class' => JsonRequestDecoder::class,
                    'default_dto_constructor_class' => SerializerDTOConstructor::class,
                    'default_dto_validator_classes' => [
                        UserIdValidator::class => null,
                    ],
                    'default_handler_wrapper_classes' => [
                        SilentExceptionWrapper::class => [
                            TasksNotAccessible::class,
                        ],
                    ],
                    'default_response_constructor_class' => SerializerJsonResponseConstructor::class,
                ],
            ],
        ];

        // -- Act
        $config = $processor->processConfiguration(
            $configuration,
            $configurationData,
        );

        // -- Assert

        // Command controller
        self::assertSame(JsonRequestDecoder::class, $config['command']['default_request_decoder_class']);
        self::assertSame(SerializerDTOConstructor::class, $config['command']['default_dto_constructor_class']);
        self::assertArrayHasKey(UserIdValidator::class, $config['command']['default_dto_validator_classes']);
        self::assertSame(
            [
                ConnectionTransactionWrapper::class => null,
                SilentExceptionWrapper::class => [
                    NewsArticleAlreadyExists::class,
                ],
            ],
            $config['command']['default_handler_wrapper_classes'],
        );
        self::assertSame(EmptyJsonResponseConstructor::class, $config['command']['default_response_constructor_class']);

        // Query controller
        self::assertSame(JsonRequestDecoder::class, $config['query']['default_request_decoder_class']);
        self::assertSame(SerializerDTOConstructor::class, $config['query']['default_dto_constructor_class']);
        self::assertArrayHasKey(UserIdValidator::class, $config['query']['default_dto_validator_classes']);
        self::assertSame(
            [
                SilentExceptionWrapper::class => [
                    TasksNotAccessible::class,
                ],
            ],
            $config['query']['default_handler_wrapper_classes'],
        );
        self::assertSame(
            SerializerJsonResponseConstructor::class,
            $config['query']['default_response_constructor_class'],
        );
    }
}
