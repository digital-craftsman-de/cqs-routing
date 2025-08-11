<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Routing;

use DigitalCraftsman\CQSRouting\Command\Command;
use DigitalCraftsman\CQSRouting\Command\CommandHandler;
use DigitalCraftsman\CQSRouting\DTOConstructor\DTOConstructor;
use DigitalCraftsman\CQSRouting\DTOValidator\DTOValidator;
use DigitalCraftsman\CQSRouting\HandlerWrapper\HandlerWrapper;
use DigitalCraftsman\CQSRouting\Query\Query;
use DigitalCraftsman\CQSRouting\Query\QueryHandler;
use DigitalCraftsman\CQSRouting\RequestDataTransformer\RequestDataTransformer;
use DigitalCraftsman\CQSRouting\RequestDecoder\RequestDecoder;
use DigitalCraftsman\CQSRouting\RequestValidator\RequestValidator;
use DigitalCraftsman\CQSRouting\ResponseConstructor\ResponseConstructor;

/**
 * The symfony routing does not support the usage of objects as it has to dump them into a php file for caching. Therefore, we create an
 * object and convert into and from an array. The validation is done through the RouteBuilder at build time (cache warmup).
 *
 * @psalm-type NormalizedConfigurationParameters = scalar|array<array-key, scalar|array<array-key, scalar|array<array-key, scalar|null>|null>|null>|null
 */
final readonly class RoutePayload
{
    /**
     * @param class-string<Command>|class-string<Query>                                           $dtoClass
     * @param class-string<CommandHandler>|class-string<QueryHandler>                             $handlerClass
     * @param array<class-string<RequestValidator>, NormalizedConfigurationParameters>|null       $requestValidatorClasses
     * @param array<class-string<RequestValidator>, NormalizedConfigurationParameters>|null       $requestValidatorClassesToMergeWithDefault
     * @param class-string<RequestDecoder>|null                                                   $requestDecoderClass
     * @param array<class-string<RequestDataTransformer>, NormalizedConfigurationParameters>|null $requestDataTransformerClasses
     * @param array<class-string<RequestDataTransformer>, NormalizedConfigurationParameters>|null $requestDataTransformerClassesToMergeWithDefault
     * @param class-string<DTOConstructor>|null                                                   $dtoConstructorClass
     * @param array<class-string<DTOValidator>, NormalizedConfigurationParameters>|null           $dtoValidatorClasses
     * @param array<class-string<DTOValidator>, NormalizedConfigurationParameters>|null           $dtoValidatorClassesToMergeWithDefault
     * @param array<class-string<HandlerWrapper>, NormalizedConfigurationParameters>|null         $handlerWrapperClasses
     * @param array<class-string<HandlerWrapper>, NormalizedConfigurationParameters>|null         $handlerWrapperClassesToMergeWithDefault
     * @param class-string<ResponseConstructor>|null                                              $responseConstructorClass
     *
     * @internal
     */
    public function __construct(
        public string $dtoClass,
        public string $handlerClass,
        public ?array $requestValidatorClasses = null,
        public ?array $requestValidatorClassesToMergeWithDefault = null,
        public ?string $requestDecoderClass = null,
        public ?array $requestDataTransformerClasses = null,
        public ?array $requestDataTransformerClassesToMergeWithDefault = null,
        public ?string $dtoConstructorClass = null,
        public ?array $dtoValidatorClasses = null,
        public ?array $dtoValidatorClassesToMergeWithDefault = null,
        public ?array $handlerWrapperClasses = null,
        public ?array $handlerWrapperClassesToMergeWithDefault = null,
        public ?string $responseConstructorClass = null,
    ) {
    }

    /**
     * @param class-string<Command>|class-string<Query>                                           $dtoClass
     * @param class-string<CommandHandler>|class-string<QueryHandler>                             $handlerClass
     * @param array<class-string<RequestValidator>, NormalizedConfigurationParameters>|null       $requestValidatorClasses
     * @param array<class-string<RequestValidator>, NormalizedConfigurationParameters>|null       $requestValidatorClassesToMergeWithDefault
     * @param class-string<RequestDecoder>|null                                                   $requestDecoderClass
     * @param array<class-string<RequestDataTransformer>, NormalizedConfigurationParameters>|null $requestDataTransformerClasses
     * @param array<class-string<RequestDataTransformer>, NormalizedConfigurationParameters>|null $requestDataTransformerClassesToMergeWithDefault
     * @param class-string<DTOConstructor>|null                                                   $dtoConstructorClass
     * @param array<class-string<DTOValidator>, NormalizedConfigurationParameters>|null           $dtoValidatorClasses
     * @param array<class-string<DTOValidator>, NormalizedConfigurationParameters>|null           $dtoValidatorClassesToMergeWithDefault
     * @param array<class-string<HandlerWrapper>, NormalizedConfigurationParameters>|null         $handlerWrapperClasses
     * @param array<class-string<HandlerWrapper>, NormalizedConfigurationParameters>|null         $handlerWrapperClassesToMergeWithDefault
     * @param class-string<ResponseConstructor>|null                                              $responseConstructorClass
     *
     * @return array{
     *   dtoClass: class-string<Command>|class-string<Query>,
     *   handlerClass: class-string<CommandHandler>|class-string<QueryHandler>,
     *   requestValidatorClasses: array<class-string<RequestValidator>, NormalizedConfigurationParameters>,
     *   requestValidatorClassesToMergeWithDefault: array<class-string<RequestValidator>, NormalizedConfigurationParameters>,
     *   requestDecoderClass: class-string<RequestDecoder>|null,
     *   requestDataTransformerClasses: array<class-string<RequestDataTransformer>, NormalizedConfigurationParameters>,
     *   requestDataTransformerClassesToMergeWithDefault: array<class-string<RequestDataTransformer>, NormalizedConfigurationParameters>,
     *   dtoConstructorClass: class-string<DTOConstructor>|null,
     *   dtoValidatorClasses: array<class-string<DTOValidator>, NormalizedConfigurationParameters>,
     *   dtoValidatorClassesToMergeWithDefault: array<class-string<DTOValidator>, NormalizedConfigurationParameters>,
     *   handlerWrapperClasses: array<class-string<HandlerWrapper>, NormalizedConfigurationParameters>,
     *   handlerWrapperClassesToMergeWithDefault: array<class-string<HandlerWrapper>, NormalizedConfigurationParameters>,
     *   responseConstructorClass: class-string<ResponseConstructor>|null,
     * }
     *
     *@internal
     */
    public static function generatePayload(
        string $dtoClass,
        string $handlerClass,
        ?array $requestValidatorClasses = null,
        ?array $requestValidatorClassesToMergeWithDefault = null,
        ?string $requestDecoderClass = null,
        ?array $requestDataTransformerClasses = null,
        ?array $requestDataTransformerClassesToMergeWithDefault = null,
        ?string $dtoConstructorClass = null,
        ?array $dtoValidatorClasses = null,
        ?array $dtoValidatorClassesToMergeWithDefault = null,
        ?array $handlerWrapperClasses = null,
        ?array $handlerWrapperClassesToMergeWithDefault = null,
        ?string $responseConstructorClass = null,
    ): array {
        $routePayload = new self(
            $dtoClass,
            $handlerClass,
            $requestValidatorClasses,
            $requestValidatorClassesToMergeWithDefault,
            $requestDecoderClass,
            $requestDataTransformerClasses,
            $requestDataTransformerClassesToMergeWithDefault,
            $dtoConstructorClass,
            $dtoValidatorClasses,
            $dtoValidatorClassesToMergeWithDefault,
            $handlerWrapperClasses,
            $handlerWrapperClassesToMergeWithDefault,
            $responseConstructorClass,
        );

        return $routePayload->toPayload();
    }

    /**
     * @param array{
     *   dtoClass: class-string<Command>|class-string<Query>,
     *   handlerClass: class-string<CommandHandler>|class-string<QueryHandler>,
     *   requestValidatorClasses: array<class-string<RequestValidator>, NormalizedConfigurationParameters>|null,
     *   requestValidatorClassesToMergeWithDefault: array<class-string<RequestValidator>, NormalizedConfigurationParameters>|null,
     *   requestDecoderClass: class-string<RequestDecoder>|null,
     *   requestDataTransformerClasses: array<class-string<RequestDataTransformer>, NormalizedConfigurationParameters>|null,
     *   requestDataTransformerClassesToMergeWithDefault: array<class-string<RequestDataTransformer>, NormalizedConfigurationParameters>|null,
     *   dtoConstructorClass: class-string<DTOConstructor>|null,
     *   dtoValidatorClasses: array<class-string<DTOValidator>, NormalizedConfigurationParameters>|null,
     *   dtoValidatorClassesToMergeWithDefault: array<class-string<DTOValidator>, NormalizedConfigurationParameters>|null,
     *   handlerWrapperClasses: array<class-string<HandlerWrapper>, NormalizedConfigurationParameters>|null,
     *   handlerWrapperClassesToMergeWithDefault: array<class-string<HandlerWrapper>, NormalizedConfigurationParameters>|null,
     *   responseConstructorClass: class-string<ResponseConstructor>|null,
     * } $payload
     *
     * @internal
     */
    public static function fromPayload(array $payload): self
    {
        return new self(
            $payload['dtoClass'],
            $payload['handlerClass'],
            $payload['requestValidatorClasses'],
            $payload['requestValidatorClassesToMergeWithDefault'],
            $payload['requestDecoderClass'],
            $payload['requestDataTransformerClasses'],
            $payload['requestDataTransformerClassesToMergeWithDefault'],
            $payload['dtoConstructorClass'],
            $payload['dtoValidatorClasses'],
            $payload['dtoValidatorClassesToMergeWithDefault'],
            $payload['handlerWrapperClasses'],
            $payload['handlerWrapperClassesToMergeWithDefault'],
            $payload['responseConstructorClass'],
        );
    }

    /**
     * @return array{
     *   dtoClass: class-string<Command>|class-string<Query>,
     *   handlerClass: class-string<CommandHandler>|class-string<QueryHandler>,
     *   requestValidatorClasses: array<class-string<RequestValidator>, NormalizedConfigurationParameters>,
     *   requestValidatorClassesToMergeWithDefault: array<class-string<RequestValidator>, NormalizedConfigurationParameters>,
     *   requestDecoderClass: class-string<RequestDecoder>|null,
     *   requestDataTransformerClasses: array<class-string<RequestDataTransformer>, NormalizedConfigurationParameters>,
     *   requestDataTransformerClassesToMergeWithDefault: array<class-string<RequestDataTransformer>, NormalizedConfigurationParameters>,
     *   dtoConstructorClass: class-string<DTOConstructor>|null,
     *   dtoValidatorClasses: array<class-string<DTOValidator>, NormalizedConfigurationParameters>,
     *   dtoValidatorClassesToMergeWithDefault: array<class-string<DTOValidator>, NormalizedConfigurationParameters>,
     *   handlerWrapperClasses: array<class-string<HandlerWrapper>, NormalizedConfigurationParameters>,
     *   handlerWrapperClassesToMergeWithDefault: array<class-string<HandlerWrapper>, NormalizedConfigurationParameters>,
     *   responseConstructorClass: class-string<ResponseConstructor>|null,
     * }
     *
     * @internal
     */
    private function toPayload(): array
    {
        return [
            'dtoClass' => $this->dtoClass,
            'handlerClass' => $this->handlerClass,
            'requestValidatorClasses' => $this->requestValidatorClasses,
            'requestValidatorClassesToMergeWithDefault' => $this->requestValidatorClassesToMergeWithDefault,
            'requestDecoderClass' => $this->requestDecoderClass,
            'requestDataTransformerClasses' => $this->requestDataTransformerClasses,
            'requestDataTransformerClassesToMergeWithDefault' => $this->requestDataTransformerClassesToMergeWithDefault,
            'dtoConstructorClass' => $this->dtoConstructorClass,
            'dtoValidatorClasses' => $this->dtoValidatorClasses,
            'dtoValidatorClassesToMergeWithDefault' => $this->dtoValidatorClassesToMergeWithDefault,
            'handlerWrapperClasses' => $this->handlerWrapperClasses,
            'handlerWrapperClassesToMergeWithDefault' => $this->handlerWrapperClassesToMergeWithDefault,
            'responseConstructorClass' => $this->responseConstructorClass,
        ];
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
     *
     * @codeCoverageIgnore We don't test this method because it's identical to the handler wrapper merge and that's tested thoroughly.
     */
    public static function mergeRequestValidatorClassesFromRouteWithDefaults(
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
     *
     * @codeCoverageIgnore We don't test this method because it's identical to the handler wrapper merge and that's tested thoroughly.
     */
    public static function mergeRequestDataTransformerClassesFromRouteWithDefaults(
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
     *
     * @codeCoverageIgnore We don't test this method because it's identical to the handler wrapper merge and that's tested thoroughly.
     */
    public static function mergeDTOValidatorClassesFromRouteWithDefaults(
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
    public static function mergeHandlerWrapperClassesFromRouteWithDefaults(
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
