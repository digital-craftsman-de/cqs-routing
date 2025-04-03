<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\DependencyInjection;

use DigitalCraftsman\CQSRouting\Command\CommandHandler;
use DigitalCraftsman\CQSRouting\DTOConstructor\DTOConstructor;
use DigitalCraftsman\CQSRouting\DTOValidator\DTOValidator;
use DigitalCraftsman\CQSRouting\HandlerWrapper\HandlerWrapper;
use DigitalCraftsman\CQSRouting\Query\QueryHandler;
use DigitalCraftsman\CQSRouting\RequestDataTransformer\RequestDataTransformer;
use DigitalCraftsman\CQSRouting\RequestDecoder\RequestDecoder;
use DigitalCraftsman\CQSRouting\RequestValidator\RequestValidator;
use DigitalCraftsman\CQSRouting\ResponseConstructor\ResponseConstructor;
use DigitalCraftsman\CQSRouting\Routing\RouteBuilder;
use DigitalCraftsman\CQSRouting\Routing\RoutePayload;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * @psalm-import-type NormalizedConfigurationParameters from RoutePayload
 */
final class CQSRoutingExtension extends Extension
{
    #[\Override]
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $container
            ->registerForAutoconfiguration(RequestValidator::class)
            ->addTag('cqs_routing.request_validator');

        $container
            ->registerForAutoconfiguration(RequestDecoder::class)
            ->addTag('cqs_routing.request_decoder');

        $container
            ->registerForAutoconfiguration(RequestDataTransformer::class)
            ->addTag('cqs_routing.request_data_transformer');

        $container
            ->registerForAutoconfiguration(DTOConstructor::class)
            ->addTag('cqs_routing.dto_constructor');

        $container
            ->registerForAutoconfiguration(DTOValidator::class)
            ->addTag('cqs_routing.dto_validator');

        $container
            ->registerForAutoconfiguration(HandlerWrapper::class)
            ->addTag('cqs_routing.handler_wrapper');

        $container
            ->registerForAutoconfiguration(CommandHandler::class)
            ->addTag('cqs_routing.command_handler');

        $container
            ->registerForAutoconfiguration(QueryHandler::class)
            ->addTag('cqs_routing.query_handler');

        $container
            ->registerForAutoconfiguration(ResponseConstructor::class)
            ->addTag('cqs_routing.response_constructor');

        $configuration = new Configuration();

        /**
         * @var array{
         *   query_controller: array{
         *     default_request_validator_classes: array<class-string<RequestValidator>, NormalizedConfigurationParameters>|null,
         *     default_request_decoder_class: class-string<RequestDecoder>|null,
         *     default_request_data_transformer_classes: array<class-string<RequestDataTransformer>, NormalizedConfigurationParameters>|null,
         *     default_dto_constructor_class: class-string<DTOConstructor>|null,
         *     default_dto_validator_classes: array<class-string<DTOValidator>, NormalizedConfigurationParameters>|null,
         *     default_handler_wrapper_classes: array<class-string<HandlerWrapper>, NormalizedConfigurationParameters>|null,
         *     default_response_constructor_class: class-string<ResponseConstructor>|null,
         *   },
         *   command_controller: array{
         *     default_request_validator_classes: array<class-string<RequestValidator>, NormalizedConfigurationParameters>|null,
         *     default_request_decoder_class: class-string<RequestDecoder>|null,
         *     default_request_data_transformer_classes: array<class-string<RequestDataTransformer>, NormalizedConfigurationParameters>|null,
         *     default_dto_constructor_class: class-string<DTOConstructor>|null,
         *     default_dto_validator_classes: array<class-string<DTOValidator>, NormalizedConfigurationParameters>|null,
         *     default_handler_wrapper_classes: array<class-string<HandlerWrapper>, NormalizedConfigurationParameters>|null,
         *     default_response_constructor_class: class-string<ResponseConstructor>|null,
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
