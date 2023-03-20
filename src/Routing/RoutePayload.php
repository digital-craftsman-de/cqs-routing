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
use DigitalCraftsman\CQRS\Routing\Exception\ClassIsNetherCommandHandlerNorQueryHandler;
use DigitalCraftsman\CQRS\Routing\Exception\ClassIsNetherCommandNorQuery;
use DigitalCraftsman\CQRS\Routing\Exception\ClassIsNoDTOConstructor;
use DigitalCraftsman\CQRS\Routing\Exception\ClassIsNoDTOValidator;
use DigitalCraftsman\CQRS\Routing\Exception\ClassIsNoHandlerWrapper;
use DigitalCraftsman\CQRS\Routing\Exception\ClassIsNoRequestDataTransformer;
use DigitalCraftsman\CQRS\Routing\Exception\ClassIsNoRequestDecoder;
use DigitalCraftsman\CQRS\Routing\Exception\ClassIsNoRequestValidator;
use DigitalCraftsman\CQRS\Routing\Exception\ClassIsNoResponseConstructor;
use DigitalCraftsman\CQRS\Routing\Exception\InvalidClassInRoutePayload;
use DigitalCraftsman\CQRS\Routing\Exception\InvalidParametersInRoutePayload;
use DigitalCraftsman\CQRS\Routing\Exception\OnlyOverwriteOrMergeCanBeUsedInRoutePayload;

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
        public readonly string $dtoClass,
        public readonly string $handlerClass,
        public readonly ?array $requestValidatorClasses = null,
        public readonly ?array $requestValidatorClassesToMergeWithDefault = null,
        public readonly ?string $requestDecoderClass = null,
        public readonly ?array $requestDataTransformerClasses = null,
        public readonly ?array $requestDataTransformerClassesToMergeWithDefault = null,
        public readonly ?string $dtoConstructorClass = null,
        public readonly ?array $dtoValidatorClasses = null,
        public readonly ?array $dtoValidatorClassesToMergeWithDefault = null,
        public readonly ?array $handlerWrapperClasses = null,
        public readonly ?array $handlerWrapperClassesToMergeWithDefault = null,
        public readonly ?string $responseConstructorClass = null,
    ) {
        self::validateDTOClass($this->dtoClass);
        self::validateHandlerClass($this->handlerClass);
        self::validateRequestValidatorClasses(
            $this->requestValidatorClasses,
            $this->requestValidatorClassesToMergeWithDefault,
        );
        self::validateRequestDecoderClass($this->requestDecoderClass);
        self::validateRequestDataTransformerClasses(
            $this->requestDataTransformerClasses,
            $this->requestDataTransformerClassesToMergeWithDefault,
        );
        self::validateDTOConstructorClass($this->dtoConstructorClass);
        self::validateDTOValidatorClasses(
            $this->dtoValidatorClasses,
            $this->dtoValidatorClassesToMergeWithDefault,
        );
        self::validateHandlerWrapperClasses(
            $this->handlerWrapperClasses,
            $this->handlerWrapperClassesToMergeWithDefault,
        );
        self::validateResponseConstructorClass($this->responseConstructorClass);
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
    public static function generate(
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
    public static function fromRouteParameters(RouteParameters $parameters): array
    {
        return self::generate(
            dtoClass: $parameters->dtoClass,
            handlerClass: $parameters->handlerClass,
            requestValidatorClasses: $parameters->requestValidatorClasses,
            requestValidatorClassesToMergeWithDefault: $parameters->requestValidatorClassesToMergeWithDefault,
            requestDecoderClass: $parameters->requestDecoderClass,
            requestDataTransformerClasses: $parameters->requestDataTransformerClasses,
            requestDataTransformerClassesToMergeWithDefault: $parameters->requestDataTransformerClassesToMergeWithDefault,
            dtoConstructorClass: $parameters->dtoConstructorClass,
            dtoValidatorClasses: $parameters->dtoValidatorClasses,
            dtoValidatorClassesToMergeWithDefault: $parameters->dtoValidatorClassesToMergeWithDefault,
            handlerWrapperClasses: $parameters->handlerWrapperClasses,
            handlerWrapperClassesToMergeWithDefault: $parameters->handlerWrapperClassesToMergeWithDefault,
            responseConstructorClass: $parameters->responseConstructorClass,
        );
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
     * @param class-string<Command|Query> $dtoClass
     *
     * @internal
     */
    public static function validateDTOClass(string $dtoClass): void
    {
        if (!class_exists($dtoClass)) {
            throw new InvalidClassInRoutePayload($dtoClass, ['dtoClass']);
        }

        $reflectionClass = new \ReflectionClass($dtoClass);
        if (!$reflectionClass->implementsInterface(Command::class)
            && !$reflectionClass->implementsInterface(Query::class)
        ) {
            throw new ClassIsNetherCommandNorQuery($dtoClass);
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
            throw new InvalidClassInRoutePayload($handlerClass, ['handlerClass']);
        }

        $reflectionClass = new \ReflectionClass($handlerClass);
        if (!$reflectionClass->implementsInterface(CommandHandlerInterface::class)
            && !$reflectionClass->implementsInterface(QueryHandlerInterface::class)
        ) {
            throw new ClassIsNetherCommandHandlerNorQueryHandler($handlerClass);
        }
    }

    /**
     * @param array<class-string<RequestValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null $requestValidatorClasses
     * @param array<class-string<RequestValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null $requestValidatorClassesToMergeWithDefault
     *
     * @internal
     */
    public static function validateRequestValidatorClasses(
        ?array $requestValidatorClasses,
        ?array $requestValidatorClassesToMergeWithDefault,
    ): void {
        if ($requestValidatorClasses !== null
            && $requestValidatorClassesToMergeWithDefault !== null
        ) {
            throw new OnlyOverwriteOrMergeCanBeUsedInRoutePayload();
        }

        $classesToValidate = $requestValidatorClasses
            ?? $requestValidatorClassesToMergeWithDefault
            ?? [];

        foreach ($classesToValidate as $class => $parameters) {
            if (!is_string($class)) {
                throw new InvalidClassInRoutePayload($class, [
                    'requestValidatorClasses',
                    'requestValidatorClassesToMergeWithDefault',
                ]);
            }

            if (!class_exists($class)) {
                throw new InvalidClassInRoutePayload($class, [
                    'requestValidatorClasses',
                    'requestValidatorClassesToMergeWithDefault',
                ]);
            }

            $reflectionClass = new \ReflectionClass($class);
            if (!$reflectionClass->implementsInterface(RequestValidatorInterface::class)) {
                throw new ClassIsNoRequestValidator($class);
            }

            if (!$class::areParametersValid($parameters)) {
                throw new InvalidParametersInRoutePayload($class);
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
        if ($requestDecoderClass !== null) {
            if (!class_exists($requestDecoderClass)) {
                throw new InvalidClassInRoutePayload($requestDecoderClass, ['requestDecoderClass']);
            }

            $reflectionClass = new \ReflectionClass($requestDecoderClass);
            if (!$reflectionClass->implementsInterface(RequestDecoderInterface::class)) {
                throw new ClassIsNoRequestDecoder($requestDecoderClass);
            }
        }
    }

    /**
     * @param array<class-string<RequestDataTransformerInterface>, scalar|array<array-key, scalar|null>|null>|null $requestDataTransformerClasses
     * @param array<class-string<RequestDataTransformerInterface>, scalar|array<array-key, scalar|null>|null>|null $requestDataTransformerClassesToMergeWithDefault
     *
     * @internal
     */
    public static function validateRequestDataTransformerClasses(
        ?array $requestDataTransformerClasses,
        ?array $requestDataTransformerClassesToMergeWithDefault,
    ): void {
        if ($requestDataTransformerClasses !== null
            && $requestDataTransformerClassesToMergeWithDefault !== null
        ) {
            throw new OnlyOverwriteOrMergeCanBeUsedInRoutePayload();
        }

        $classesToValidate = $requestDataTransformerClasses
            ?? $requestDataTransformerClassesToMergeWithDefault
            ?? [];

        foreach ($classesToValidate as $class => $parameters) {
            if (!is_string($class)) {
                throw new InvalidClassInRoutePayload($class, [
                    'requestDataTransformerClasses',
                    'requestDataTransformerClassesToMergeWithDefault',
                ]);
            }

            if (!class_exists($class)) {
                throw new InvalidClassInRoutePayload($class, [
                    'requestDataTransformerClasses',
                    'requestDataTransformerClassesToMergeWithDefault',
                ]);
            }

            $reflectionClass = new \ReflectionClass($class);
            if (!$reflectionClass->implementsInterface(RequestDataTransformerInterface::class)) {
                throw new ClassIsNoRequestDataTransformer($class);
            }

            if (!$class::areParametersValid($parameters)) {
                throw new InvalidParametersInRoutePayload($class);
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
        if ($dtoConstructorClass !== null) {
            if (!class_exists($dtoConstructorClass)) {
                throw new InvalidClassInRoutePayload($dtoConstructorClass, ['dtoConstructorClass']);
            }

            $reflectionClass = new \ReflectionClass($dtoConstructorClass);
            if (!$reflectionClass->implementsInterface(DTOConstructorInterface::class)) {
                throw new ClassIsNoDTOConstructor($dtoConstructorClass);
            }
        }
    }

    /**
     * @param array<class-string<DTOValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null $dtoValidatorClasses
     * @param array<class-string<DTOValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null $dtoValidatorClassesToMergeWithDefault
     *
     * @internal
     */
    public static function validateDTOValidatorClasses(
        ?array $dtoValidatorClasses,
        ?array $dtoValidatorClassesToMergeWithDefault,
    ): void {
        if ($dtoValidatorClasses !== null
            && $dtoValidatorClassesToMergeWithDefault !== null
        ) {
            throw new OnlyOverwriteOrMergeCanBeUsedInRoutePayload();
        }

        $classesToValidate = $dtoValidatorClasses
            ?? $dtoValidatorClassesToMergeWithDefault
            ?? [];

        foreach ($classesToValidate as $class => $parameters) {
            if (!is_string($class)) {
                throw new InvalidClassInRoutePayload($class, [
                    'dtoValidatorClasses',
                    'dtoValidatorClassesToMergeWithDefault',
                ]);
            }

            if (!class_exists($class)) {
                throw new InvalidClassInRoutePayload($class, [
                    'dtoValidatorClasses',
                    'dtoValidatorClassesToMergeWithDefault',
                ]);
            }

            $reflectionClass = new \ReflectionClass($class);
            if (!$reflectionClass->implementsInterface(DTOValidatorInterface::class)) {
                throw new ClassIsNoDTOValidator($class);
            }

            if (!$class::areParametersValid($parameters)) {
                throw new InvalidParametersInRoutePayload($class);
            }
        }
    }

    /**
     * @param array<class-string<HandlerWrapperInterface>, scalar|array<array-key, scalar|bool|null>|null>|null $handlerWrapperClasses
     * @param array<class-string<HandlerWrapperInterface>, scalar|array<array-key, scalar|bool|null>|null>|null $handlerWrapperClassesToMergeWithDefault
     *
     * @internal
     */
    public static function validateHandlerWrapperClasses(
        ?array $handlerWrapperClasses,
        ?array $handlerWrapperClassesToMergeWithDefault,
    ): void {
        if ($handlerWrapperClasses !== null
            && $handlerWrapperClassesToMergeWithDefault !== null
        ) {
            throw new OnlyOverwriteOrMergeCanBeUsedInRoutePayload();
        }

        $classesToValidate = $handlerWrapperClasses
            ?? $handlerWrapperClassesToMergeWithDefault
            ?? [];

        foreach ($classesToValidate as $class => $parameters) {
            if (!is_string($class)) {
                throw new InvalidClassInRoutePayload($class, [
                    'handlerWrapperClasses',
                    'handlerWrapperClassesToMergeWithDefault',
                ]);
            }

            if (!class_exists($class)) {
                throw new InvalidClassInRoutePayload($class, [
                    'handlerWrapperClasses',
                    'handlerWrapperClassesToMergeWithDefault',
                ]);
            }

            $reflectionClass = new \ReflectionClass($class);
            if (!$reflectionClass->implementsInterface(HandlerWrapperInterface::class)) {
                throw new ClassIsNoHandlerWrapper($class);
            }

            if (!$class::areParametersValid($parameters)) {
                throw new InvalidParametersInRoutePayload($class);
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
        if ($responseConstructorClass !== null) {
            if (!class_exists($responseConstructorClass)) {
                throw new InvalidClassInRoutePayload($responseConstructorClass, ['responseConstructorClass']);
            }

            $reflectionClass = new \ReflectionClass($responseConstructorClass);
            if (!$reflectionClass->implementsInterface(ResponseConstructorInterface::class)) {
                throw new ClassIsNoResponseConstructor($responseConstructorClass);
            }
        }
    }

    /**
     * Classes with parameters are taken from request configuration if available.
     * Otherwise, the ones from the route that should be merged with default are merged with the default. The parameters of the list to
     * merge with default are used when the same class is used in the default and the ones to merge.
     *
     * @template T of RequestValidatorInterface|RequestDataTransformerInterface|DTOValidatorInterface|HandlerWrapperInterface
     *
     * @param array<class-string<T>, scalar|array<array-key, scalar|null>|null>|null $classesFromRoute
     * @param array<class-string<T>, scalar|array<array-key, scalar|null>|null>|null $classesFromRouteToMergeWithDefault
     * @param array<class-string<T>, scalar|array<array-key, scalar|null>|null>|null $classesFromDefault
     *
     * @return array<class-string<T>, scalar|array<array-key, scalar|null>|null>
     *
     * @internal
     */
    public static function mergeClassesFromRouteWithDefaults(
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
