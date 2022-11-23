<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\DependencyInjection;

use DigitalCraftsman\CQRS\Command\CommandHandlerInterface;
use DigitalCraftsman\CQRS\DTOConstructor\DTOConstructorInterface;
use DigitalCraftsman\CQRS\DTOValidator\DTOValidatorInterface;
use DigitalCraftsman\CQRS\HandlerWrapper\HandlerWrapperInterface;
use DigitalCraftsman\CQRS\Query\QueryHandlerInterface;
use DigitalCraftsman\CQRS\RequestDataTransformer\RequestDataTransformerInterface;
use DigitalCraftsman\CQRS\RequestDecoder\RequestDecoderInterface;
use DigitalCraftsman\CQRS\RequestValidator\RequestValidatorInterface;
use DigitalCraftsman\CQRS\ResponseConstructor\ResponseConstructorInterface;
use DigitalCraftsman\CQRS\ValueObject\RoutePayload;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class CQRSExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $container
            ->registerForAutoconfiguration(RequestValidatorInterface::class)
            ->addTag('cqrs.request_validator');

        $container
            ->registerForAutoconfiguration(RequestDecoderInterface::class)
            ->addTag('cqrs.request_decoder');

        $container
            ->registerForAutoconfiguration(RequestDataTransformerInterface::class)
            ->addTag('cqrs.request_data_transformer');

        $container
            ->registerForAutoconfiguration(DTOConstructorInterface::class)
            ->addTag('cqrs.dto_constructor');

        $container
            ->registerForAutoconfiguration(DTOValidatorInterface::class)
            ->addTag('cqrs.dto_validator');

        $container
            ->registerForAutoconfiguration(HandlerWrapperInterface::class)
            ->addTag('cqrs.handler_wrapper');

        $container
            ->registerForAutoconfiguration(CommandHandlerInterface::class)
            ->addTag('cqrs.command_handler');

        $container
            ->registerForAutoconfiguration(QueryHandlerInterface::class)
            ->addTag('cqrs.query_handler');

        $container
            ->registerForAutoconfiguration(ResponseConstructorInterface::class)
            ->addTag('cqrs.response_constructor');

        $configuration = new Configuration();

        /**
         * @var array{
         *   query_controller: array{
         *     default_request_validator_classes: array<class-string<RequestValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null,
         *     default_request_decoder_class: string|null,
         *     default_request_data_transformer_classes: array<class-string<RequestDataTransformerInterface>, scalar|array<array-key, scalar|null>|null>|null,
         *     default_dto_constructor_class: string|null,
         *     default_dto_validator_classes: array<class-string<DTOValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null,
         *     default_handler_wrapper_classes: array<class-string<HandlerWrapperInterface>, scalar|array<array-key, scalar|null>|null>|null,
         *     default_response_constructor_class: string|null,
         *   },
         *   command_controller: array{
         *     default_request_validator_classes: array<class-string<RequestValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null,
         *     default_request_decoder_class: string|null,
         *     default_request_data_transformer_classes: array<class-string<RequestDataTransformerInterface>, scalar|array<array-key, scalar|null>|null>|null,
         *     default_dto_constructor_class: string|null,
         *     default_dto_validator_classes: array<class-string<DTOValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null,
         *     default_handler_wrapper_classes: array<class-string<HandlerWrapperInterface>, scalar|array<array-key, scalar|null>|null>|null,
         *     default_response_constructor_class: string|null,
         *   },
         *   serializer_context: array,
         * } $config
         */
        $config = $this->processConfiguration($configuration, $configs);

        RoutePayload::validateRequestValidatorClasses(
            $config['query_controller']['default_request_validator_classes'],
            null,
        );
        RoutePayload::validateRequestDecoderClass($config['query_controller']['default_request_decoder_class']);
        RoutePayload::validateRequestDataTransformerClasses(
            $config['query_controller']['default_request_data_transformer_classes'],
            null,
        );
        RoutePayload::validateDTOConstructorClass($config['query_controller']['default_dto_constructor_class']);
        RoutePayload::validateDTOValidatorClasses(
            $config['query_controller']['default_dto_validator_classes'],
            null,
        );
        RoutePayload::validateHandlerWrapperClasses(
            $config['query_controller']['default_handler_wrapper_classes'],
            null,
        );
        RoutePayload::validateResponseConstructorClass($config['query_controller']['default_response_constructor_class']);

        RoutePayload::validateRequestValidatorClasses(
            $config['command_controller']['default_request_validator_classes'],
            null,
        );
        RoutePayload::validateRequestDecoderClass($config['command_controller']['default_request_decoder_class']);
        RoutePayload::validateRequestDataTransformerClasses(
            $config['command_controller']['default_request_data_transformer_classes'],
            null,
        );
        RoutePayload::validateDTOConstructorClass($config['command_controller']['default_dto_constructor_class']);
        RoutePayload::validateDTOValidatorClasses(
            $config['command_controller']['default_dto_validator_classes'],
            null,
        );
        RoutePayload::validateHandlerWrapperClasses(
            $config['command_controller']['default_handler_wrapper_classes'],
            null,
        );
        RoutePayload::validateResponseConstructorClass($config['command_controller']['default_response_constructor_class']);

        foreach ($config['query_controller'] as $key => $value) {
            $container->setParameter('cqrs.query_controller.'.$key, $value);
        }

        foreach ($config['command_controller'] as $key => $value) {
            $container->setParameter('cqrs.command_controller.'.$key, $value);
        }

        $container->setParameter('cqrs.serializer_context', $config['serializer_context']);
    }
}
