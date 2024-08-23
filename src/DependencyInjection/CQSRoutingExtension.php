<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\DependencyInjection;

use DigitalCraftsman\CQSRouting\Command\CommandHandlerInterface;
use DigitalCraftsman\CQSRouting\DTOConstructor\DTOConstructorInterface;
use DigitalCraftsman\CQSRouting\DTOValidator\DTOValidatorInterface;
use DigitalCraftsman\CQSRouting\HandlerWrapper\HandlerWrapperInterface;
use DigitalCraftsman\CQSRouting\Query\QueryHandlerInterface;
use DigitalCraftsman\CQSRouting\RequestDataTransformer\RequestDataTransformerInterface;
use DigitalCraftsman\CQSRouting\RequestDecoder\RequestDecoderInterface;
use DigitalCraftsman\CQSRouting\RequestValidator\RequestValidatorInterface;
use DigitalCraftsman\CQSRouting\ResponseConstructor\ResponseConstructorInterface;
use DigitalCraftsman\CQSRouting\Routing\RouteBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class CQSRoutingExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $container
            ->registerForAutoconfiguration(RequestValidatorInterface::class)
            ->addTag('cqs_routing.request_validator');

        $container
            ->registerForAutoconfiguration(RequestDecoderInterface::class)
            ->addTag('cqs_routing.request_decoder');

        $container
            ->registerForAutoconfiguration(RequestDataTransformerInterface::class)
            ->addTag('cqs_routing.request_data_transformer');

        $container
            ->registerForAutoconfiguration(DTOConstructorInterface::class)
            ->addTag('cqs_routing.dto_constructor');

        $container
            ->registerForAutoconfiguration(DTOValidatorInterface::class)
            ->addTag('cqs_routing.dto_validator');

        $container
            ->registerForAutoconfiguration(HandlerWrapperInterface::class)
            ->addTag('cqs_routing.handler_wrapper');

        $container
            ->registerForAutoconfiguration(CommandHandlerInterface::class)
            ->addTag('cqs_routing.command_handler');

        $container
            ->registerForAutoconfiguration(QueryHandlerInterface::class)
            ->addTag('cqs_routing.query_handler');

        $container
            ->registerForAutoconfiguration(ResponseConstructorInterface::class)
            ->addTag('cqs_routing.response_constructor');

        $configuration = new Configuration();

        /**
         * @var array{
         *   query_controller: array{
         *     default_request_validator_classes: array<class-string<RequestValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null,
         *     default_request_decoder_class: class-string<RequestDecoderInterface>|null,
         *     default_request_data_transformer_classes: array<class-string<RequestDataTransformerInterface>, scalar|array<array-key, scalar|null>|null>|null,
         *     default_dto_constructor_class: class-string<DTOConstructorInterface>|null,
         *     default_dto_validator_classes: array<class-string<DTOValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null,
         *     default_handler_wrapper_classes: array<class-string<HandlerWrapperInterface>, scalar|array<array-key, scalar|null>|null>|null,
         *     default_response_constructor_class: class-string<ResponseConstructorInterface>|null,
         *   },
         *   command_controller: array{
         *     default_request_validator_classes: array<class-string<RequestValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null,
         *     default_request_decoder_class: class-string<RequestDecoderInterface>|null,
         *     default_request_data_transformer_classes: array<class-string<RequestDataTransformerInterface>, scalar|array<array-key, scalar|null>|null>|null,
         *     default_dto_constructor_class: class-string<DTOConstructorInterface>|null,
         *     default_dto_validator_classes: array<class-string<DTOValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null,
         *     default_handler_wrapper_classes: array<class-string<HandlerWrapperInterface>, scalar|array<array-key, scalar|null>|null>|null,
         *     default_response_constructor_class: class-string<ResponseConstructorInterface>|null,
         *   },
         * } $config
         */
        $config = $this->processConfiguration($configuration, $configs);

        RouteBuilder::validateRequestValidatorClasses(
            $config['query_controller']['default_request_validator_classes'],
            null,
        );
        RouteBuilder::validateRequestDecoderClass($config['query_controller']['default_request_decoder_class']);
        RouteBuilder::validateRequestDataTransformerClasses(
            $config['query_controller']['default_request_data_transformer_classes'],
            null,
        );
        RouteBuilder::validateDTOConstructorClass($config['query_controller']['default_dto_constructor_class']);
        RouteBuilder::validateDTOValidatorClasses(
            $config['query_controller']['default_dto_validator_classes'],
            null,
        );
        RouteBuilder::validateHandlerWrapperClasses(
            $config['query_controller']['default_handler_wrapper_classes'],
            null,
        );
        RouteBuilder::validateResponseConstructorClass($config['query_controller']['default_response_constructor_class']);

        RouteBuilder::validateRequestValidatorClasses(
            $config['command_controller']['default_request_validator_classes'],
            null,
        );
        RouteBuilder::validateRequestDecoderClass($config['command_controller']['default_request_decoder_class']);
        RouteBuilder::validateRequestDataTransformerClasses(
            $config['command_controller']['default_request_data_transformer_classes'],
            null,
        );
        RouteBuilder::validateDTOConstructorClass($config['command_controller']['default_dto_constructor_class']);
        RouteBuilder::validateDTOValidatorClasses(
            $config['command_controller']['default_dto_validator_classes'],
            null,
        );
        RouteBuilder::validateHandlerWrapperClasses(
            $config['command_controller']['default_handler_wrapper_classes'],
            null,
        );
        RouteBuilder::validateResponseConstructorClass($config['command_controller']['default_response_constructor_class']);

        foreach ($config['query_controller'] as $key => $value) {
            $container->setParameter('cqs_routing.query_controller.'.$key, $value);
        }

        foreach ($config['command_controller'] as $key => $value) {
            $container->setParameter('cqs_routing.command_controller.'.$key, $value);
        }
    }
}
