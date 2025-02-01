<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Routing;

use DigitalCraftsman\CQSRouting\Command\Command;
use DigitalCraftsman\CQSRouting\Command\CommandHandlerInterface;
use DigitalCraftsman\CQSRouting\Controller\CommandController;
use DigitalCraftsman\CQSRouting\Controller\QueryController;
use DigitalCraftsman\CQSRouting\DTOConstructor\DTOConstructorInterface;
use DigitalCraftsman\CQSRouting\DTOValidator\DTOValidatorInterface;
use DigitalCraftsman\CQSRouting\HandlerWrapper\HandlerWrapperInterface;
use DigitalCraftsman\CQSRouting\Query\Query;
use DigitalCraftsman\CQSRouting\Query\QueryHandlerInterface;
use DigitalCraftsman\CQSRouting\RequestDataTransformer\RequestDataTransformerInterface;
use DigitalCraftsman\CQSRouting\RequestDecoder\RequestDecoderInterface;
use DigitalCraftsman\CQSRouting\RequestValidator\RequestValidatorInterface;
use DigitalCraftsman\CQSRouting\ResponseConstructor\ResponseConstructorInterface;
use DigitalCraftsman\CQSRouting\Routing\Exception\ClassIsNetherCommandHandlerNorQueryHandler;
use DigitalCraftsman\CQSRouting\Routing\Exception\ClassIsNetherCommandNorQuery;
use DigitalCraftsman\CQSRouting\Routing\Exception\ClassIsNoDTOConstructor;
use DigitalCraftsman\CQSRouting\Routing\Exception\ClassIsNoDTOValidator;
use DigitalCraftsman\CQSRouting\Routing\Exception\ClassIsNoHandlerWrapper;
use DigitalCraftsman\CQSRouting\Routing\Exception\ClassIsNoRequestDataTransformer;
use DigitalCraftsman\CQSRouting\Routing\Exception\ClassIsNoRequestDecoder;
use DigitalCraftsman\CQSRouting\Routing\Exception\ClassIsNoRequestValidator;
use DigitalCraftsman\CQSRouting\Routing\Exception\ClassIsNoResponseConstructor;
use DigitalCraftsman\CQSRouting\Routing\Exception\InvalidClassInRoutePayload;
use DigitalCraftsman\CQSRouting\Routing\Exception\InvalidParametersInRoutePayload;
use DigitalCraftsman\CQSRouting\Routing\Exception\OnlyOverwriteOrMergeCanBeUsedInRoutePayload;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * @psalm-import-type NormalizedConfigurationParameters from RoutePayload
 */
final readonly class RouteBuilder
{
    private const DEFAULT_METHOD = Request::METHOD_POST;

    /**
     * Helper method to reduce noise in routing.
     * Default name is generated from path. Set it specifically when you're using the name as a reference somewhere.
     * Default method is POST.
     *
     * @param class-string<Command>|class-string<Query>                                                    $dtoClass
     * @param class-string<CommandHandlerInterface>|class-string<QueryHandlerInterface>                    $handlerClass
     * @param array<class-string<RequestValidatorInterface>, NormalizedConfigurationParameters>|null       $requestValidatorClasses
     * @param array<class-string<RequestValidatorInterface>, NormalizedConfigurationParameters>|null       $requestValidatorClassesToMergeWithDefault
     * @param class-string<RequestDecoderInterface>|null                                                   $requestDecoderClass
     * @param array<class-string<RequestDataTransformerInterface>, NormalizedConfigurationParameters>|null $requestDataTransformerClasses
     * @param array<class-string<RequestDataTransformerInterface>, NormalizedConfigurationParameters>|null $requestDataTransformerClassesToMergeWithDefault
     * @param class-string<DTOConstructorInterface>|null                                                   $dtoConstructorClass
     * @param array<class-string<DTOValidatorInterface>, NormalizedConfigurationParameters>|null           $dtoValidatorClasses
     * @param array<class-string<DTOValidatorInterface>, NormalizedConfigurationParameters>|null           $dtoValidatorClassesToMergeWithDefault
     * @param array<class-string<HandlerWrapperInterface>, NormalizedConfigurationParameters>|null         $handlerWrapperClasses
     * @param array<class-string<HandlerWrapperInterface>, NormalizedConfigurationParameters>|null         $handlerWrapperClassesToMergeWithDefault
     * @param class-string<ResponseConstructorInterface>|null                                              $responseConstructorClass
     *
     * @codeCoverageIgnore
     * There seems to be no way to get a RoutingConfigurator instance. Therefore, it's not really possible to test this builder.
     */
    public static function addQueryRoute(
        RoutingConfigurator $routes,
        string $path,
        string $dtoClass,
        string $handlerClass,
        ?string $name = null,
        ?string $method = null,
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
        array $additionalRouteDefaults = [],
    ): void {
        self::validateDTOClass($dtoClass);
        self::validateHandlerClass($handlerClass);
        self::validateRequestValidatorClasses(
            $requestValidatorClasses,
            $requestValidatorClassesToMergeWithDefault,
        );
        self::validateRequestDecoderClass($requestDecoderClass);
        self::validateRequestDataTransformerClasses(
            $requestDataTransformerClasses,
            $requestDataTransformerClassesToMergeWithDefault,
        );
        self::validateDTOConstructorClass($dtoConstructorClass);
        self::validateDTOValidatorClasses(
            $dtoValidatorClasses,
            $dtoValidatorClassesToMergeWithDefault,
        );
        self::validateHandlerWrapperClasses(
            $handlerWrapperClasses,
            $handlerWrapperClassesToMergeWithDefault,
        );
        self::validateResponseConstructorClass($responseConstructorClass);

        $name = $name ?? self::generateNameFromPath($path);
        $methods = [$method ?? self::DEFAULT_METHOD];
        $defaults = array_merge(
            [
                'routePayload' => RoutePayload::generatePayload(
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
                ),
            ],
            $additionalRouteDefaults,
        );

        $routes->add(
            $name,
            $path,
        )
            ->controller([QueryController::class, 'handle'])
            ->methods($methods)
            ->defaults($defaults);
    }

    /**
     * Helper method to reduce noise in routing.
     * Default name is generated from path. Set it specifically when you're using the name as a reference somewhere.
     * Default method is POST.
     *
     * @param class-string<Command>|class-string<Query>                                                    $dtoClass
     * @param class-string<CommandHandlerInterface>|class-string<QueryHandlerInterface>                    $handlerClass
     * @param array<class-string<RequestValidatorInterface>, NormalizedConfigurationParameters>|null       $requestValidatorClasses
     * @param array<class-string<RequestValidatorInterface>, NormalizedConfigurationParameters>|null       $requestValidatorClassesToMergeWithDefault
     * @param class-string<RequestDecoderInterface>|null                                                   $requestDecoderClass
     * @param array<class-string<RequestDataTransformerInterface>, NormalizedConfigurationParameters>|null $requestDataTransformerClasses
     * @param array<class-string<RequestDataTransformerInterface>, NormalizedConfigurationParameters>|null $requestDataTransformerClassesToMergeWithDefault
     * @param class-string<DTOConstructorInterface>|null                                                   $dtoConstructorClass
     * @param array<class-string<DTOValidatorInterface>, NormalizedConfigurationParameters>|null           $dtoValidatorClasses
     * @param array<class-string<DTOValidatorInterface>, NormalizedConfigurationParameters>|null           $dtoValidatorClassesToMergeWithDefault
     * @param array<class-string<HandlerWrapperInterface>, NormalizedConfigurationParameters>|null         $handlerWrapperClasses
     * @param array<class-string<HandlerWrapperInterface>, NormalizedConfigurationParameters>|null         $handlerWrapperClassesToMergeWithDefault
     * @param class-string<ResponseConstructorInterface>|null                                              $responseConstructorClass
     *
     * @codeCoverageIgnore
     * There seems to be no way to get a RoutingConfigurator instance. Therefore, it's not really possible to test this builder.
     */
    public static function addCommandRoute(
        RoutingConfigurator $routes,
        string $path,
        string $dtoClass,
        string $handlerClass,
        ?string $name = null,
        ?string $method = null,
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
        array $additionalRouteDefaults = [],
    ): void {
        self::validateDTOClass($dtoClass);
        self::validateHandlerClass($handlerClass);
        self::validateRequestValidatorClasses(
            $requestValidatorClasses,
            $requestValidatorClassesToMergeWithDefault,
        );
        self::validateRequestDecoderClass($requestDecoderClass);
        self::validateRequestDataTransformerClasses(
            $requestDataTransformerClasses,
            $requestDataTransformerClassesToMergeWithDefault,
        );
        self::validateDTOConstructorClass($dtoConstructorClass);
        self::validateDTOValidatorClasses(
            $dtoValidatorClasses,
            $dtoValidatorClassesToMergeWithDefault,
        );
        self::validateHandlerWrapperClasses(
            $handlerWrapperClasses,
            $handlerWrapperClassesToMergeWithDefault,
        );
        self::validateResponseConstructorClass($responseConstructorClass);

        $name = $name ?? self::generateNameFromPath($path);
        $methods = [$method ?? self::DEFAULT_METHOD];
        $defaults = array_merge(
            [
                'routePayload' => RoutePayload::generatePayload(
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
                ),
            ],
            $additionalRouteDefaults,
        );

        $routes->add(
            $name,
            $path,
        )
            ->controller([CommandController::class, 'handle'])
            ->methods($methods)
            ->defaults($defaults);
    }

    public static function generateNameFromPath(string $path): string
    {
        $path = str_starts_with($path, '/')
            ? substr($path, 1)
            : $path;

        /**
         * Convert camelCase to snake_case (for example for parameters).
         *
         * @psalm-suppress PossiblyNullArgument We know that it's not going to be empty.
         */
        $path = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $path));

        // Replace / and - with _ and remove {} and duplicate __
        return str_replace(
            ['/', '-', '{', '}', '__'],
            ['_', '_', '', '', '_'],
            mb_strtolower($path),
        );
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
     * @param class-string<CommandHandlerInterface|QueryHandlerInterface> $handlerClass
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
     * @param array<class-string<RequestValidatorInterface>, NormalizedConfigurationParameters>|null $requestValidatorClasses
     * @param array<class-string<RequestValidatorInterface>, NormalizedConfigurationParameters>|null $requestValidatorClassesToMergeWithDefault
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
            /** @psalm-suppress TypeDoesNotContainType It's possible that due to configuration issues, something else is supplied. */
            if (!$reflectionClass->implementsInterface(RequestValidatorInterface::class)) {
                throw new ClassIsNoRequestValidator($class);
            }

            if (!$class::areParametersValid($parameters)) {
                throw new InvalidParametersInRoutePayload($class);
            }
        }
    }

    /**
     * @param class-string<RequestDecoderInterface>|null $requestDecoderClass
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
            /** @psalm-suppress TypeDoesNotContainType It's possible that due to configuration issues, something else is supplied. */
            if (!$reflectionClass->implementsInterface(RequestDecoderInterface::class)) {
                throw new ClassIsNoRequestDecoder($requestDecoderClass);
            }
        }
    }

    /**
     * @param array<class-string<RequestDataTransformerInterface>, NormalizedConfigurationParameters>|null $requestDataTransformerClasses
     * @param array<class-string<RequestDataTransformerInterface>, NormalizedConfigurationParameters>|null $requestDataTransformerClassesToMergeWithDefault
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
            /** @psalm-suppress TypeDoesNotContainType It's possible that due to configuration issues, something else is supplied. */
            if (!$reflectionClass->implementsInterface(RequestDataTransformerInterface::class)) {
                throw new ClassIsNoRequestDataTransformer($class);
            }

            if (!$class::areParametersValid($parameters)) {
                throw new InvalidParametersInRoutePayload($class);
            }
        }
    }

    /**
     * @param class-string<DTOConstructorInterface>|null $dtoConstructorClass
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
            /** @psalm-suppress TypeDoesNotContainType It's possible that due to configuration issues, something else is supplied. */
            if (!$reflectionClass->implementsInterface(DTOConstructorInterface::class)) {
                throw new ClassIsNoDTOConstructor($dtoConstructorClass);
            }
        }
    }

    /**
     * @param array<class-string<DTOValidatorInterface>, NormalizedConfigurationParameters>|null $dtoValidatorClasses
     * @param array<class-string<DTOValidatorInterface>, NormalizedConfigurationParameters>|null $dtoValidatorClassesToMergeWithDefault
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
            /** @psalm-suppress TypeDoesNotContainType It's possible that due to configuration issues, something else is supplied. */
            if (!$reflectionClass->implementsInterface(DTOValidatorInterface::class)) {
                throw new ClassIsNoDTOValidator($class);
            }

            if (!$class::areParametersValid($parameters)) {
                throw new InvalidParametersInRoutePayload($class);
            }
        }
    }

    /**
     * @param array<class-string<HandlerWrapperInterface>, NormalizedConfigurationParameters>|null $handlerWrapperClasses
     * @param array<class-string<HandlerWrapperInterface>, NormalizedConfigurationParameters>|null $handlerWrapperClassesToMergeWithDefault
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
            /** @psalm-suppress TypeDoesNotContainType It's possible that due to configuration issues, something else is supplied. */
            if (!$reflectionClass->implementsInterface(HandlerWrapperInterface::class)) {
                throw new ClassIsNoHandlerWrapper($class);
            }

            if (!$class::areParametersValid($parameters)) {
                throw new InvalidParametersInRoutePayload($class);
            }
        }
    }

    /**
     * @param class-string<ResponseConstructorInterface>|null $responseConstructorClass
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
            /** @psalm-suppress TypeDoesNotContainType It's possible that due to configuration issues, something else is supplied. */
            if (!$reflectionClass->implementsInterface(ResponseConstructorInterface::class)) {
                throw new ClassIsNoResponseConstructor($responseConstructorClass);
            }
        }
    }
}
