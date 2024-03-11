<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Routing;

use DigitalCraftsman\CQRS\Command\Command;
use DigitalCraftsman\CQRS\Command\CommandHandlerInterface;
use DigitalCraftsman\CQRS\DTOConstructor\DTOConstructorInterface;
use DigitalCraftsman\CQRS\DTOValidator\DTOValidatorInterface;
use DigitalCraftsman\CQRS\HandlerWrapper\HandlerWrapperInterface;
use DigitalCraftsman\CQRS\Query\Query;
use DigitalCraftsman\CQRS\Query\QueryHandlerInterface;
use DigitalCraftsman\CQRS\RequestDataTransformer\RequestDataTransformerInterface;
use DigitalCraftsman\CQRS\RequestDecoder\RequestDecoderInterface;
use DigitalCraftsman\CQRS\RequestValidator\RequestValidatorInterface;
use DigitalCraftsman\CQRS\ResponseConstructor\ResponseConstructorInterface;

/**
 * The symfony routing does not support the usage of objects as it has to dump them into a php file for caching. Therefore, we create an
 * object and convert into and from an array. The validation is done through the RouteBuilder at build time (cache warmup).
 */
final readonly class RoutePayload
{
    /**
     * @param class-string<Command>|class-string<Query>                                                            $dtoClass
     * @param class-string<CommandHandlerInterface>|class-string<QueryHandlerInterface>                            $handlerClass
     * @param array<class-string<RequestValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null       $requestValidatorClasses
     * @param array<class-string<RequestValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null       $requestValidatorClassesToMergeWithDefault
     * @param class-string<RequestDecoderInterface>|null                                                           $requestDecoderClass
     * @param array<class-string<RequestDataTransformerInterface>, scalar|array<array-key, scalar|null>|null>|null $requestDataTransformerClasses
     * @param array<class-string<RequestDataTransformerInterface>, scalar|array<array-key, scalar|null>|null>|null $requestDataTransformerClassesToMergeWithDefault
     * @param class-string<DTOConstructorInterface>|null                                                           $dtoConstructorClass
     * @param array<class-string<DTOValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null           $dtoValidatorClasses
     * @param array<class-string<DTOValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null           $dtoValidatorClassesToMergeWithDefault
     * @param array<class-string<HandlerWrapperInterface>, scalar|array<array-key, scalar|null>|null>|null         $handlerWrapperClasses
     * @param array<class-string<HandlerWrapperInterface>, scalar|array<array-key, scalar|null>|null>|null         $handlerWrapperClassesToMergeWithDefault
     * @param class-string<ResponseConstructorInterface>|null                                                      $responseConstructorClass
     */
    private function __construct(
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
     * @param class-string<Command>|class-string<Query>                                                            $dtoClass
     * @param class-string<CommandHandlerInterface>|class-string<QueryHandlerInterface>                            $handlerClass
     * @param array<class-string<RequestValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null       $requestValidatorClasses
     * @param array<class-string<RequestValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null       $requestValidatorClassesToMergeWithDefault
     * @param class-string<RequestDecoderInterface>|null                                                           $requestDecoderClass
     * @param array<class-string<RequestDataTransformerInterface>, scalar|array<array-key, scalar|null>|null>|null $requestDataTransformerClasses
     * @param array<class-string<RequestDataTransformerInterface>, scalar|array<array-key, scalar|null>|null>|null $requestDataTransformerClassesToMergeWithDefault
     * @param class-string<DTOConstructorInterface>|null                                                           $dtoConstructorClass
     * @param array<class-string<DTOValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null           $dtoValidatorClasses
     * @param array<class-string<DTOValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null           $dtoValidatorClassesToMergeWithDefault
     * @param array<class-string<HandlerWrapperInterface>, scalar|array<array-key, scalar|bool|null>|null>|null    $handlerWrapperClasses
     * @param array<class-string<HandlerWrapperInterface>, scalar|array<array-key, scalar|bool|null>|null>|null    $handlerWrapperClassesToMergeWithDefault
     * @param class-string<ResponseConstructorInterface>|null                                                      $responseConstructorClass
     *
     * @internal
     *
     * @return array{
     *   dtoClass: class-string<Command>|class-string<Query>,
     *   handlerClass: class-string<CommandHandlerInterface>|class-string<QueryHandlerInterface>,
     *   requestValidatorClasses: array<class-string<RequestValidatorInterface>, scalar|array<array-key, null|scalar>|null>,
     *   requestValidatorClassesToMergeWithDefault: array<class-string<RequestValidatorInterface>, scalar|array<array-key, null|scalar>|null>,
     *   requestDecoderClass: class-string<RequestDecoderInterface>|null,
     *   requestDataTransformerClasses: array<class-string<RequestDataTransformerInterface>, scalar|array<array-key, null|scalar>|null>,
     *   requestDataTransformerClassesToMergeWithDefault: array<class-string<RequestDataTransformerInterface>, scalar|array<array-key, null|scalar>|null>,
     *   dtoConstructorClass: class-string<DTOConstructorInterface>|null,
     *   dtoValidatorClasses: array<class-string<DTOValidatorInterface>, scalar|array<array-key, null|scalar>|null>,
     *   dtoValidatorClassesToMergeWithDefault: array<class-string<DTOValidatorInterface>, scalar|array<array-key, null|scalar>|null>,
     *   handlerWrapperClasses: array<class-string<HandlerWrapperInterface>, scalar|array<array-key, null|scalar>|null>,
     *   handlerWrapperClassesToMergeWithDefault: array<class-string<HandlerWrapperInterface>, scalar|array<array-key, null|scalar>|null>,
     *   responseConstructorClass: class-string<ResponseConstructorInterface>|null,
     * }
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
     *   handlerClass: class-string<CommandHandlerInterface>|class-string<QueryHandlerInterface>,
     *   requestValidatorClasses: array<class-string<RequestValidatorInterface>, scalar|array<array-key, null|scalar>|null>|null,
     *   requestValidatorClassesToMergeWithDefault: array<class-string<RequestValidatorInterface>, scalar|array<array-key, null|scalar>|null>|null,
     *   requestDecoderClass: class-string<RequestDecoderInterface>|null,
     *   requestDataTransformerClasses: array<class-string<RequestDataTransformerInterface>, scalar|array<array-key, null|scalar>|null>|null,
     *   requestDataTransformerClassesToMergeWithDefault: array<class-string<RequestDataTransformerInterface>, scalar|array<array-key, null|scalar>|null>|null,
     *   dtoConstructorClass: class-string<DTOConstructorInterface>|null,
     *   dtoValidatorClasses: array<class-string<DTOValidatorInterface>, scalar|array<array-key, null|scalar>|null>|null,
     *   dtoValidatorClassesToMergeWithDefault: array<class-string<DTOValidatorInterface>, scalar|array<array-key, null|scalar>|null>|null,
     *   handlerWrapperClasses: array<class-string<HandlerWrapperInterface>, scalar|array<array-key, null|scalar>|null>|null,
     *   handlerWrapperClassesToMergeWithDefault: array<class-string<HandlerWrapperInterface>, scalar|array<array-key, null|scalar>|null>|null,
     *   responseConstructorClass: class-string<ResponseConstructorInterface>|null,
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
     *   handlerClass: class-string<CommandHandlerInterface>|class-string<QueryHandlerInterface>,
     *   requestValidatorClasses: array<class-string<RequestValidatorInterface>, scalar|array<array-key, null|scalar>|null>,
     *   requestValidatorClassesToMergeWithDefault: array<class-string<RequestValidatorInterface>, scalar|array<array-key, null|scalar>|null>,
     *   requestDecoderClass: class-string<RequestDecoderInterface>|null,
     *   requestDataTransformerClasses: array<class-string<RequestDataTransformerInterface>, scalar|array<array-key, null|scalar>|null>,
     *   requestDataTransformerClassesToMergeWithDefault: array<class-string<RequestDataTransformerInterface>, scalar|array<array-key, null|scalar>|null>,
     *   dtoConstructorClass: class-string<DTOConstructorInterface>|null,
     *   dtoValidatorClasses: array<class-string<DTOValidatorInterface>, scalar|array<array-key, null|scalar>|null>,
     *   dtoValidatorClassesToMergeWithDefault: array<class-string<DTOValidatorInterface>, scalar|array<array-key, null|scalar>|null>,
     *   handlerWrapperClasses: array<class-string<HandlerWrapperInterface>, scalar|array<array-key, null|scalar>|null>,
     *   handlerWrapperClassesToMergeWithDefault: array<class-string<HandlerWrapperInterface>, scalar|array<array-key, null|scalar>|null>,
     *   responseConstructorClass: class-string<ResponseConstructorInterface>|null,
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
     * @param array<class-string<RequestValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null $classesFromRoute
     * @param array<class-string<RequestValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null $classesFromRouteToMergeWithDefault
     * @param array<class-string<RequestValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null $classesFromDefault
     *
     * @return array<class-string<RequestValidatorInterface>, scalar|array<array-key, scalar|null>|null>
     *
     * @internal
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
     * @param array<class-string<RequestDataTransformerInterface>, scalar|array<array-key, scalar|null>|null>|null $classesFromRoute
     * @param array<class-string<RequestDataTransformerInterface>, scalar|array<array-key, scalar|null>|null>|null $classesFromRouteToMergeWithDefault
     * @param array<class-string<RequestDataTransformerInterface>, scalar|array<array-key, scalar|null>|null>|null $classesFromDefault
     *
     * @return array<class-string<RequestDataTransformerInterface>, scalar|array<array-key, scalar|null>|null>
     *
     * @internal
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
     * @param array<class-string<DTOValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null $classesFromRoute
     * @param array<class-string<DTOValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null $classesFromRouteToMergeWithDefault
     * @param array<class-string<DTOValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null $classesFromDefault
     *
     * @return array<class-string<DTOValidatorInterface>, scalar|array<array-key, scalar|null>|null>
     *
     * @internal
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
     * @param array<class-string<HandlerWrapperInterface>, scalar|array<array-key, scalar|null>|null>|null $classesFromRoute
     * @param array<class-string<HandlerWrapperInterface>, scalar|array<array-key, scalar|null>|null>|null $classesFromRouteToMergeWithDefault
     * @param array<class-string<HandlerWrapperInterface>, scalar|array<array-key, scalar|null>|null>|null $classesFromDefault
     *
     * @return array<class-string<HandlerWrapperInterface>, scalar|array<array-key, scalar|null>|null>
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
