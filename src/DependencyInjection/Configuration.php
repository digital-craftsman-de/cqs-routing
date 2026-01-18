<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * @psalm-suppress MixedMethodCall
     * @psalm-suppress UndefinedMethod
     */
    #[\Override]
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('cqs_routing');
        $rootNode = $treeBuilder->getRootNode();

        /** @psalm-suppress PossiblyUndefinedMethod */
        $rootNode
            ->children()
                ->arrayNode('command')
                    ->info('Add default instances for command routes')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('default_request_validator_classes')
                            ->beforeNormalization()->castToArray()->end()
                            ->defaultValue([])
                            ->prototype('variable')->end()
                        ->end()
                        ->scalarNode('default_request_decoder_class')->defaultNull()->end()
                        ->arrayNode('default_request_data_transformer_classes')
                            ->beforeNormalization()->castToArray()->end()
                            ->defaultValue([])
                            ->prototype('variable')->end()
                        ->end()
                        ->scalarNode('default_dto_constructor_class')->defaultNull()->end()
                        ->arrayNode('default_dto_validator_classes')
                            ->beforeNormalization()->castToArray()->end()
                            ->useAttributeAsKey('class')
                            ->variablePrototype()->end()
                            ->defaultValue([])
                        ->end()
                        ->arrayNode('default_handler_wrapper_classes')
                            ->beforeNormalization()->castToArray()->end()
                            ->defaultValue([])
                            ->prototype('variable')->end()
                        ->end()
                        ->scalarNode('default_response_constructor_class')->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode('query')
                    ->info('Add default instances for query routes')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('default_request_validator_classes')
                            ->beforeNormalization()->castToArray()->end()
                            ->defaultValue([])
                            ->prototype('variable')->end()
                        ->end()
                        ->scalarNode('default_request_decoder_class')->defaultNull()->end()
                        ->arrayNode('default_request_data_transformer_classes')
                            ->beforeNormalization()->castToArray()->end()
                            ->defaultValue([])
                            ->prototype('variable')->end()
                        ->end()
                        ->scalarNode('default_dto_constructor_class')->defaultNull()->end()
                        ->arrayNode('default_dto_validator_classes')
                            ->beforeNormalization()->castToArray()->end()
                            ->defaultValue([])
                            ->prototype('variable')->end()
                        ->end()
                        ->arrayNode('default_handler_wrapper_classes')
                            ->beforeNormalization()->castToArray()->end()
                            ->defaultValue([])
                            ->prototype('variable')->end()
                        ->end()
                        ->scalarNode('default_response_constructor_class')->defaultNull()->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
