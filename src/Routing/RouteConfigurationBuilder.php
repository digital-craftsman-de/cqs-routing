<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Routing;

use DigitalCraftsman\CQSRouting\DTOConstructor\DTOConstructor;
use DigitalCraftsman\CQSRouting\DTOValidator\DTOValidator;
use DigitalCraftsman\CQSRouting\HandlerWrapper\HandlerWrapper;
use DigitalCraftsman\CQSRouting\RequestDataTransformer\RequestDataTransformer;
use DigitalCraftsman\CQSRouting\RequestDecoder\RequestDecoder;
use DigitalCraftsman\CQSRouting\RequestValidator\RequestValidator;
use DigitalCraftsman\CQSRouting\ResponseConstructor\ResponseConstructor;

/**
 * The configuration is the result of the route payload in combination with the defaults to be used by the controller.
 *
 * @psalm-import-type NormalizedConfigurationParameters from RoutePayload
 */
final readonly class RouteConfigurationBuilder
{
    /**
     * @param array<class-string<RequestValidator>, NormalizedConfigurationParameters>|null       $defaultRequestValidatorClassesForCommand
     * @param class-string<RequestDecoder>|null                                                   $defaultRequestDecoderClassForCommand
     * @param array<class-string<RequestDataTransformer>, NormalizedConfigurationParameters>|null $defaultRequestDataTransformerClassesForCommand
     * @param class-string<DTOConstructor>|null                                                   $defaultDTOConstructorClassForCommand
     * @param array<class-string<DTOValidator>, NormalizedConfigurationParameters>|null           $defaultDTOValidatorClassesForCommand
     * @param array<class-string<HandlerWrapper>, NormalizedConfigurationParameters>|null         $defaultHandlerWrapperClassesForCommand
     * @param class-string<ResponseConstructor>|null                                              $defaultResponseConstructorClassForCommand
     * @param array<class-string<RequestValidator>, NormalizedConfigurationParameters>|null       $defaultRequestValidatorClassesForQuery
     * @param class-string<RequestDecoder>|null                                                   $defaultRequestDecoderClassForQuery
     * @param array<class-string<RequestDataTransformer>, NormalizedConfigurationParameters>|null $defaultRequestDataTransformerClassesForQuery
     * @param class-string<DTOConstructor>|null                                                   $defaultDTOConstructorClassForQuery
     * @param array<class-string<DTOValidator>, NormalizedConfigurationParameters>|null           $defaultDTOValidatorClassesForQuery
     * @param array<class-string<HandlerWrapper>, NormalizedConfigurationParameters>|null         $defaultHandlerWrapperClassesForQuery
     * @param class-string<ResponseConstructor>|null                                              $defaultResponseConstructorClassForQuery
     *
     * @codeCoverageIgnore
     */
    public function __construct(
        private ?array $defaultRequestValidatorClassesForCommand = null,
        private ?string $defaultRequestDecoderClassForCommand = null,
        private ?array $defaultRequestDataTransformerClassesForCommand = null,
        private ?string $defaultDTOConstructorClassForCommand = null,
        private ?array $defaultDTOValidatorClassesForCommand = null,
        private ?array $defaultHandlerWrapperClassesForCommand = null,
        private ?string $defaultResponseConstructorClassForCommand = null,
        private ?array $defaultRequestValidatorClassesForQuery = null,
        private ?string $defaultRequestDecoderClassForQuery = null,
        private ?array $defaultRequestDataTransformerClassesForQuery = null,
        private ?string $defaultDTOConstructorClassForQuery = null,
        private ?array $defaultDTOValidatorClassesForQuery = null,
        private ?array $defaultHandlerWrapperClassesForQuery = null,
        private ?string $defaultResponseConstructorClassForQuery = null,
    ) {
    }

    public function buildConfigurationForCommand(RoutePayload $routePayload): RouteConfiguration
    {
        return self::buildConfigurationWithDefaults(
            $routePayload,
            $this->defaultRequestValidatorClassesForCommand,
            $this->defaultRequestDecoderClassForCommand,
            $this->defaultRequestDataTransformerClassesForCommand,
            $this->defaultDTOConstructorClassForCommand,
            $this->defaultDTOValidatorClassesForCommand,
            $this->defaultHandlerWrapperClassesForCommand,
            $this->defaultResponseConstructorClassForCommand,
        );
    }

    public function buildConfigurationForQuery(RoutePayload $routePayload): RouteConfiguration
    {
        return self::buildConfigurationWithDefaults(
            $routePayload,
            $this->defaultRequestValidatorClassesForQuery,
            $this->defaultRequestDecoderClassForQuery,
            $this->defaultRequestDataTransformerClassesForQuery,
            $this->defaultDTOConstructorClassForQuery,
            $this->defaultDTOValidatorClassesForQuery,
            $this->defaultHandlerWrapperClassesForQuery,
            $this->defaultResponseConstructorClassForQuery,
        );
    }

    /**
     * @param array<class-string<RequestValidator>, NormalizedConfigurationParameters>|null       $defaultRequestValidatorClasses
     * @param class-string<RequestDecoder>|null                                                   $defaultRequestDecoderClass
     * @param array<class-string<RequestDataTransformer>, NormalizedConfigurationParameters>|null $defaultRequestDataTransformerClasses
     * @param class-string<DTOConstructor>|null                                                   $defaultDTOConstructorClass
     * @param array<class-string<DTOValidator>, NormalizedConfigurationParameters>|null           $defaultDTOValidatorClasses
     * @param array<class-string<HandlerWrapper>, NormalizedConfigurationParameters>|null         $defaultHandlerWrapperClasses
     * @param class-string<ResponseConstructor>|null                                              $defaultResponseConstructorClass
     */
    private static function buildConfigurationWithDefaults(
        RoutePayload $routePayload,
        ?array $defaultRequestValidatorClasses,
        ?string $defaultRequestDecoderClass,
        ?array $defaultRequestDataTransformerClasses,
        ?string $defaultDTOConstructorClass,
        ?array $defaultDTOValidatorClasses,
        ?array $defaultHandlerWrapperClasses,
        ?string $defaultResponseConstructorClass,
    ): RouteConfiguration {
        $requestDecoderClass = $routePayload->requestDecoderClass ?? $defaultRequestDecoderClass;
        if ($requestDecoderClass === null) {
            throw new Exception\RequestDecoderOrDefaultRequestDecoderMustBeConfigured();
        }

        $requestValidatorClasses = self::mergeRequestValidatorClassesFromRouteWithDefaults(
            $routePayload->requestValidatorClasses,
            $routePayload->requestValidatorClassesToMergeWithDefault,
            $defaultRequestValidatorClasses,
        );

        $requestDataTransformerClasses = self::mergeRequestDataTransformerClassesFromRouteWithDefaults(
            $routePayload->requestDataTransformerClasses,
            $routePayload->requestDataTransformerClassesToMergeWithDefault,
            $defaultRequestDataTransformerClasses,
        );

        $dtoConstructorClass = $routePayload->dtoConstructorClass ?? $defaultDTOConstructorClass;
        if ($dtoConstructorClass === null) {
            throw new Exception\DTOConstructorOrDefaultDTOConstructorMustBeConfigured();
        }

        $responseConstructorClass = $routePayload->responseConstructorClass ?? $defaultResponseConstructorClass;
        if ($responseConstructorClass === null) {
            throw new Exception\ResponseConstructorOrDefaultResponseConstructorMustBeConfigured();
        }

        $dtoValidatorClasses = self::mergeDTOValidatorClassesFromRouteWithDefaults(
            $routePayload->dtoValidatorClasses,
            $routePayload->dtoValidatorClassesToMergeWithDefault,
            $defaultDTOValidatorClasses,
        );

        $handlerWrapperClasses = self::mergeHandlerWrapperClassesFromRouteWithDefaults(
            $routePayload->handlerWrapperClasses,
            $routePayload->handlerWrapperClassesToMergeWithDefault,
            $defaultHandlerWrapperClasses,
        );

        return new RouteConfiguration(
            dtoClass: $routePayload->dtoClass,
            handlerClass: $routePayload->handlerClass,
            requestValidatorClasses: $requestValidatorClasses,
            requestDecoderClass: $requestDecoderClass,
            requestDataTransformerClasses: $requestDataTransformerClasses,
            dtoConstructorClass: $dtoConstructorClass,
            dtoValidatorClasses: $dtoValidatorClasses,
            handlerWrapperClasses: $handlerWrapperClasses,
            responseConstructorClass: $responseConstructorClass,
        );
    }

    /**
     * Classes with parameters are taken from request configuration if available.
     * Otherwise, the ones from the route that should be merged with default are merged with the default. The parameters of the list to
     * merge with default are used when the same class is used in the default and the ones to merge.
     *
     * @param array<class-string<RequestValidator>, NormalizedConfigurationParameters>|null $classesFromRoute
     * @param array<class-string<RequestValidator>, NormalizedConfigurationParameters>|null $classesFromRouteToMergeWithDefault
     * @param array<class-string<RequestValidator>, NormalizedConfigurationParameters>|null $classesFromDefault
     *
     * @return array<class-string<RequestValidator>, NormalizedConfigurationParameters>
     *
     * @internal
     */
    private static function mergeRequestValidatorClassesFromRouteWithDefaults(
        ?array $classesFromRoute,
        ?array $classesFromRouteToMergeWithDefault,
        ?array $classesFromDefault,
    ): array {
        return $classesFromRoute ?? array_merge(
            $classesFromDefault ?? [],
            $classesFromRouteToMergeWithDefault ?? [],
        );
    }

    /**
     * Classes with parameters are taken from request configuration if available.
     * Otherwise, the ones from the route that should be merged with default are merged with the default. The parameters of the list to
     * merge with default are used when the same class is used in the default and the ones to merge.
     *
     * @param array<class-string<RequestDataTransformer>, NormalizedConfigurationParameters>|null $classesFromRoute
     * @param array<class-string<RequestDataTransformer>, NormalizedConfigurationParameters>|null $classesFromRouteToMergeWithDefault
     * @param array<class-string<RequestDataTransformer>, NormalizedConfigurationParameters>|null $classesFromDefault
     *
     * @return array<class-string<RequestDataTransformer>, NormalizedConfigurationParameters>
     *
     * @internal
     */
    private static function mergeRequestDataTransformerClassesFromRouteWithDefaults(
        ?array $classesFromRoute,
        ?array $classesFromRouteToMergeWithDefault,
        ?array $classesFromDefault,
    ): array {
        return $classesFromRoute ?? array_merge(
            $classesFromDefault ?? [],
            $classesFromRouteToMergeWithDefault ?? [],
        );
    }

    /**
     * Classes with parameters are taken from request configuration if available.
     * Otherwise, the ones from the route that should be merged with default are merged with the default. The parameters of the list to
     * merge with default are used when the same class is used in the default and the ones to merge.
     *
     * @param array<class-string<DTOValidator>, NormalizedConfigurationParameters>|null $classesFromRoute
     * @param array<class-string<DTOValidator>, NormalizedConfigurationParameters>|null $classesFromRouteToMergeWithDefault
     * @param array<class-string<DTOValidator>, NormalizedConfigurationParameters>|null $classesFromDefault
     *
     * @return array<class-string<DTOValidator>, NormalizedConfigurationParameters>
     *
     * @internal
     */
    private static function mergeDTOValidatorClassesFromRouteWithDefaults(
        ?array $classesFromRoute,
        ?array $classesFromRouteToMergeWithDefault,
        ?array $classesFromDefault,
    ): array {
        return $classesFromRoute ?? array_merge(
            $classesFromDefault ?? [],
            $classesFromRouteToMergeWithDefault ?? [],
        );
    }

    /**
     * Classes with parameters are taken from request configuration if available.
     * Otherwise, the ones from the route that should be merged with default are merged with the default. The parameters of the list to
     * merge with default are used when the same class is used in the default and the ones to merge.
     *
     * @param array<class-string<HandlerWrapper>, NormalizedConfigurationParameters>|null $classesFromRoute
     * @param array<class-string<HandlerWrapper>, NormalizedConfigurationParameters>|null $classesFromRouteToMergeWithDefault
     * @param array<class-string<HandlerWrapper>, NormalizedConfigurationParameters>|null $classesFromDefault
     *
     * @return array<class-string<HandlerWrapper>, NormalizedConfigurationParameters>
     *
     * @internal
     */
    private static function mergeHandlerWrapperClassesFromRouteWithDefaults(
        ?array $classesFromRoute,
        ?array $classesFromRouteToMergeWithDefault,
        ?array $classesFromDefault,
    ): array {
        return $classesFromRoute ?? array_merge(
            $classesFromDefault ?? [],
            $classesFromRouteToMergeWithDefault ?? [],
        );
    }
}
