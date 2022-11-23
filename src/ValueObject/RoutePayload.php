<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ValueObject;

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
use DigitalCraftsman\CQRS\ValueObject\Exception\InvalidClassInRoutePayload;
use DigitalCraftsman\CQRS\ValueObject\Exception\InvalidParametersInRoutePayload;

/**
 * The symfony routing does not support the usage of objects as it has to dump them into a php file for caching. Therefore, we create an
 * object and convert into and from an array. This also enables us to validate the routing on build time (cache warmup).
 */
final class RoutePayload
{
    /**
     * @param class-string<Command>|class-string<Query>                                                            $dtoClass
     * @param class-string<CommandHandlerInterface>|class-string<QueryHandlerInterface>                            $handlerClass
     * @param array<class-string<RequestValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null       $requestValidatorClasses
     * @param class-string<RequestDecoderInterface>|null                                                           $requestDecoderClass
     * @param array<class-string<RequestDataTransformerInterface>, scalar|array<array-key, scalar|null>|null>|null $requestDataTransformerClasses
     * @param class-string<DTOConstructorInterface>|null                                                           $dtoConstructorClass
     * @param array<class-string<DTOValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null           $dtoValidatorClasses
     * @param array<class-string<HandlerWrapperInterface>, scalar|array<array-key, scalar|null>|null>|null         $handlerWrapperClasses
     * @param class-string<ResponseConstructorInterface>|null                                                      $responseConstructorClass
     */
    private function __construct(
        public readonly string $dtoClass,
        public readonly string $handlerClass,
        public readonly ?array $requestValidatorClasses = null,
        public readonly ?string $requestDecoderClass = null,
        public readonly ?array $requestDataTransformerClasses = null,
        public readonly ?string $dtoConstructorClass = null,
        public readonly ?array $dtoValidatorClasses = null,
        public readonly ?array $handlerWrapperClasses = null,
        public readonly ?string $responseConstructorClass = null,
    ) {
        self::validateDTOClass($this->dtoClass);
        self::validateHandlerClass($this->handlerClass);
        self::validateRequestValidatorClasses($this->requestValidatorClasses);
        self::validateRequestDecoderClass($this->requestDecoderClass);
        self::validateRequestDataTransformerClasses($this->requestDataTransformerClasses);
        self::validateDTOConstructorClass($this->dtoConstructorClass);
        self::validateDTOValidatorClasses($this->dtoValidatorClasses);
        self::validateHandlerWrapperClasses($this->handlerWrapperClasses);
        self::validateResponseConstructorClass($this->responseConstructorClass);
    }

    /**
     * @param class-string<Command>|class-string<Query>                                                            $dtoClass
     * @param class-string<CommandHandlerInterface>|class-string<QueryHandlerInterface>                            $handlerClass
     * @param array<class-string<RequestValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null       $requestValidatorClasses
     * @param class-string<RequestDecoderInterface>|null                                                           $requestDecoderClass
     * @param array<class-string<RequestDataTransformerInterface>, scalar|array<array-key, scalar|null>|null>|null $requestDataTransformerClasses
     * @param class-string<DTOConstructorInterface>|null                                                           $dtoConstructorClass
     * @param array<class-string<DTOValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null           $dtoValidatorClasses
     * @param array<class-string<HandlerWrapperInterface>, scalar|array<array-key, scalar|bool|null>|null>|null    $handlerWrapperClasses
     * @param class-string<ResponseConstructorInterface>|null                                                      $responseConstructorClass
     */
    public static function generate(
        string $dtoClass,
        string $handlerClass,
        ?array $requestValidatorClasses = null,
        ?string $requestDecoderClass = null,
        ?array $requestDataTransformerClasses = null,
        ?string $dtoConstructorClass = null,
        ?array $dtoValidatorClasses = null,
        ?array $handlerWrapperClasses = null,
        ?string $responseConstructorClass = null,
    ): array {
        $routePayload = new self(
            $dtoClass,
            $handlerClass,
            $requestValidatorClasses,
            $requestDecoderClass,
            $requestDataTransformerClasses,
            $dtoConstructorClass,
            $dtoValidatorClasses,
            $handlerWrapperClasses,
            $responseConstructorClass,
        );

        return $routePayload->toPayload();
    }

    /**
     * @param array{
     *   dtoClass: class-string<Command>|class-string<Query>,
     *   handlerClass: class-string<CommandHandlerInterface>|class-string<QueryHandlerInterface>,
     *   requestValidatorClasses: array<class-string<RequestValidatorInterface>, scalar|array<array-key, null|scalar>|null>,
     *   requestDecoderClass: class-string<RequestDecoderInterface>|null,
     *   requestDataTransformerClasses: array<class-string<RequestDataTransformerInterface>, scalar|array<array-key, null|scalar>|null>,
     *   dtoConstructorClass: class-string<DTOConstructorInterface>|null,
     *   dtoValidatorClasses: array<class-string<DTOValidatorInterface>, scalar|array<array-key, null|scalar>|null>,
     *   handlerWrapperClasses: array<class-string<HandlerWrapperInterface>, scalar|array<array-key, null|scalar>|null>,
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
            $payload['requestDecoderClass'],
            $payload['requestDataTransformerClasses'],
            $payload['dtoConstructorClass'],
            $payload['dtoValidatorClasses'],
            $payload['handlerWrapperClasses'],
            $payload['responseConstructorClass'],
        );
    }

    /**
     * @return array{
     *   dtoClass: class-string<Command>|class-string<Query>,
     *   handlerClass: class-string<CommandHandlerInterface>|class-string<QueryHandlerInterface>,
     *   requestValidatorClasses: array<class-string<RequestValidatorInterface>, scalar|array<array-key, null|scalar>|null>,
     *   requestDecoderClass: class-string<RequestDecoderInterface>|null,
     *   requestDataTransformerClasses: array<class-string<RequestDataTransformerInterface>, scalar|array<array-key, null|scalar>|null>,
     *   dtoConstructorClass: class-string<DTOConstructorInterface>|null,
     *   dtoValidatorClasses: array<class-string<DTOValidatorInterface>, scalar|array<array-key, null|scalar>|null>,
     *   handlerWrapperClasses: array<class-string<HandlerWrapperInterface>, scalar|array<array-key, null|scalar>|null>,
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
            'requestDecoderClass' => $this->requestDecoderClass,
            'requestDataTransformerClasses' => $this->requestDataTransformerClasses,
            'dtoConstructorClass' => $this->dtoConstructorClass,
            'dtoValidatorClasses' => $this->dtoValidatorClasses,
            'handlerWrapperClasses' => $this->handlerWrapperClasses,
            'responseConstructorClass' => $this->responseConstructorClass,
        ];
    }

    /**
     * @param class-string<Command|Query> $dtoClass
     *
     * @internal
     */
    public static function validateDTOClass(string $dtoClass): void
    {
        if (!class_exists($dtoClass)) {
            throw new InvalidClassInRoutePayload($dtoClass);
        }
    }

    /**
     * @param class-string<CommandHandlerInterface|QueryHandlerInterface> $dtoClass
     *
     * @internal
     */
    public static function validateHandlerClass(string $handlerClass): void
    {
        if (!class_exists($handlerClass)) {
            throw new InvalidClassInRoutePayload($handlerClass);
        }
    }

    /**
     * @param array<class-string<RequestValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null $requestValidatorClasses
     *
     * @internal
     */
    public static function validateRequestValidatorClasses(?array $requestValidatorClasses): void
    {
        if ($requestValidatorClasses !== null) {
            foreach ($requestValidatorClasses as $class => $parameters) {
                if (!is_string($class)) {
                    throw new InvalidClassInRoutePayload($class);
                }

                if (!class_exists($class)) {
                    throw new InvalidClassInRoutePayload($class);
                }

                if (!$class::areParametersValid($parameters)) {
                    throw new InvalidParametersInRoutePayload($class);
                }
            }
        }
    }

    /**
     * @param class-string<RequestDecoderInterface> $dtoClass
     *
     * @internal
     */
    public static function validateRequestDecoderClass(?string $requestDecoderClass): void
    {
        if ($requestDecoderClass !== null
            && !class_exists($requestDecoderClass)
        ) {
            throw new InvalidClassInRoutePayload($requestDecoderClass);
        }
    }

    /**
     * @param array<class-string<RequestDataTransformerInterface>, scalar|array<array-key, scalar|null>|null>|null $requestDataTransformerClasses
     *
     * @internal
     */
    public static function validateRequestDataTransformerClasses(?array $requestDataTransformerClasses): void
    {
        if ($requestDataTransformerClasses !== null) {
            foreach ($requestDataTransformerClasses as $class => $parameters) {
                if (!is_string($class)) {
                    throw new InvalidClassInRoutePayload($class);
                }

                if (!class_exists($class)) {
                    throw new InvalidClassInRoutePayload($class);
                }

                if (!$class::areParametersValid($parameters)) {
                    throw new InvalidParametersInRoutePayload($class);
                }
            }
        }
    }

    /**
     * @param class-string<DTOConstructorInterface> $dtoClass
     *
     * @internal
     */
    public static function validateDTOConstructorClass(?string $dtoConstructorClass): void
    {
        if ($dtoConstructorClass !== null
            && !class_exists($dtoConstructorClass)
        ) {
            throw new InvalidClassInRoutePayload($dtoConstructorClass);
        }
    }

    /**
     * @param array<class-string<DTOValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null $dtoValidatorClasses
     *
     * @internal
     */
    public static function validateDTOValidatorClasses(?array $dtoValidatorClasses): void
    {
        if ($dtoValidatorClasses !== null) {
            foreach ($dtoValidatorClasses as $class => $parameters) {
                if (!is_string($class)) {
                    throw new InvalidClassInRoutePayload($class);
                }

                if (!class_exists($class)) {
                    throw new InvalidClassInRoutePayload($class);
                }

                if (!$class::areParametersValid($parameters)) {
                    throw new InvalidParametersInRoutePayload($class);
                }
            }
        }
    }

    /**
     * @param array<class-string<HandlerWrapperInterface>, scalar|array<array-key, scalar|bool|null>|null>|null $handlerWrapperClasses
     *
     * @internal
     */
    public static function validateHandlerWrapperClasses(?array $handlerWrapperClasses): void
    {
        if ($handlerWrapperClasses !== null) {
            foreach ($handlerWrapperClasses as $class => $parameters) {
                if (!is_string($class)) {
                    throw new InvalidClassInRoutePayload($class);
                }

                if (!class_exists($class)) {
                    throw new InvalidClassInRoutePayload($class);
                }

                if (!$class::areParametersValid($parameters)) {
                    throw new InvalidParametersInRoutePayload($class);
                }
            }
        }
    }

    /**
     * @param class-string<ResponseConstructorInterface> $dtoClass
     *
     * @internal
     */
    public static function validateResponseConstructorClass(?string $responseConstructorClass): void
    {
        if ($responseConstructorClass !== null
            && !class_exists($responseConstructorClass)
        ) {
            throw new InvalidClassInRoutePayload($responseConstructorClass);
        }
    }

    /**
     * Classes with parameters are taken from request configuration if available. Otherwise, the ones from default are used.
     *
     * @template T of RequestValidatorInterface|RequestDataTransformerInterface|DTOValidatorInterface|HandlerWrapperInterface
     *
     * @param array<class-string<T>, scalar|array<array-key, scalar|null>|null>|null $classesFromRoute
     * @param array<class-string<T>, scalar|array<array-key, scalar|null>|null>|null $classesFromDefault
     *
     * @return array<class-string<T>, scalar|array<array-key, scalar|null>|null>
     *
     * @internal
     */
    public static function mergeClassesFromRouteWithDefaults(
        ?array $classesFromRoute,
        ?array $classesFromDefault,
    ): array {
        return $classesFromRoute
            ?? $classesFromDefault
            ?? [];
    }
}
