<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\DependencyInjection;

use DigitalCraftsman\CQRS\DTOConstructor\SerializerDTOConstructor;
use DigitalCraftsman\CQRS\RequestDecoder\JsonRequestDecoder;
use DigitalCraftsman\CQRS\ResponseConstructor\EmptyJsonResponseConstructor;
use DigitalCraftsman\CQRS\ResponseConstructor\SerializerJsonResponseConstructor;
use DigitalCraftsman\CQRS\Test\Application\Authentication\UserIdValidator;
use DigitalCraftsman\CQRS\Test\Application\ConnectionTransactionWrapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

/** @coversDefaultClass \DigitalCraftsman\CQRS\DependencyInjection\Configuration */
final class ConfigurationTest extends TestCase
{
    /**
     * @test
     * @covers ::getConfigTreeBuilder
     */
    public function configuration_can_be_loaded(): void
    {
        // -- Arrange
        $processor = new Processor();
        $configuration = new Configuration();
        $configurationData = [
            'cqrs' => [
                'command_controller' => [
                    'default_request_decoder_class' => JsonRequestDecoder::class,
                    'default_dto_constructor_class' => SerializerDTOConstructor::class,
                    'default_dto_validator_classes' => [
                        UserIdValidator::class,
                    ],
                    'default_handler_wrapper_classes' => [
                        ConnectionTransactionWrapper::class,
                    ],
                    'default_response_constructor_class' => EmptyJsonResponseConstructor::class,
                ],
                'query_controller' => [
                    'default_request_decoder_class' => JsonRequestDecoder::class,
                    'default_dto_constructor_class' => SerializerDTOConstructor::class,
                    'default_dto_validator_classes' => [
                        UserIdValidator::class,
                    ],
                    'default_handler_wrapper_classes' => [],
                    'default_response_constructor_class' => SerializerJsonResponseConstructor::class,
                ],
                'serializer_context' => [
                    'skip_null_values' => true,
                    'preserve_empty_objects' => true,
                ],
            ],
        ];

        // -- Act
        $config = $processor->processConfiguration(
            $configuration,
            $configurationData,
        );

        // -- Assert
        self::assertSame(JsonRequestDecoder::class, $config['command_controller']['default_request_decoder_class']);
        self::assertSame(SerializerDTOConstructor::class, $config['command_controller']['default_dto_constructor_class']);
        self::assertContains(UserIdValidator::class, $config['command_controller']['default_dto_validator_classes']);
        self::assertContains(ConnectionTransactionWrapper::class, $config['command_controller']['default_handler_wrapper_classes']);
        self::assertSame(EmptyJsonResponseConstructor::class, $config['command_controller']['default_response_constructor_class']);

        self::assertSame(JsonRequestDecoder::class, $config['query_controller']['default_request_decoder_class']);
        self::assertSame(SerializerDTOConstructor::class, $config['query_controller']['default_dto_constructor_class']);
        self::assertContains(UserIdValidator::class, $config['query_controller']['default_dto_validator_classes']);
        self::assertCount(0, $config['query_controller']['default_handler_wrapper_classes']);
        self::assertSame(
            SerializerJsonResponseConstructor::class,
            $config['query_controller']['default_response_constructor_class'],
        );

        self::assertTrue($config['serializer_context']['skip_null_values']);
        self::assertTrue($config['serializer_context']['preserve_empty_objects']);
    }
}
