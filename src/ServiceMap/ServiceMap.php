<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ServiceMap;

use DigitalCraftsman\CQRS\Command\CommandHandlerInterface;
use DigitalCraftsman\CQRS\DTO\HandlerWrapperConfiguration;
use DigitalCraftsman\CQRS\DTOConstructor\DTOConstructorInterface;
use DigitalCraftsman\CQRS\DTODataTransformer\DTODataTransformerInterface;
use DigitalCraftsman\CQRS\DTOValidator\DTOValidatorInterface;
use DigitalCraftsman\CQRS\HandlerWrapper\DTO\HandlerWrapperWithParameters;
use DigitalCraftsman\CQRS\HandlerWrapper\HandlerWrapperInterface;
use DigitalCraftsman\CQRS\Query\QueryHandlerInterface;
use DigitalCraftsman\CQRS\RequestDecoder\RequestDecoderInterface;
use DigitalCraftsman\CQRS\ResponseConstructor\ResponseConstructorInterface;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredCommandHandlerNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredDTOConstructorNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredDTODataTransformerNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredDTOValidatorNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredHandlerWrapperNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredQueryHandlerNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredRequestDecoderNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredResponseConstructorNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\NoDefaultDTOConstructorDefined;
use DigitalCraftsman\CQRS\ServiceMap\Exception\NoDefaultResponseConstructorDefined;
use DigitalCraftsman\CQRS\ServiceMap\Exception\RequestDecoderOrDefaultRequestDecoderMustBeConfigured;

/** @internal */
final class ServiceMap
{
    /** @var array<string, RequestDecoderInterface> */
    private array $requestDecoderMap = [];

    /** @var array<string, DTODataTransformerInterface> */
    private array $dtoDataTransformerMap = [];

    /** @var array<string, DTOConstructorInterface> */
    private array $dtoConstructorMap = [];

    /** @var array<string, DTOValidatorInterface> */
    private array $dtoValidatorMap = [];

    /** @var array<string, HandlerWrapperInterface> */
    private array $handlerWrapperMap = [];

    /** @var array<string, CommandHandlerInterface> */
    private array $commandHandlerMap = [];

    /** @var array<string, QueryHandlerInterface> */
    private array $queryHandlerMap = [];

    /** @var array<string, ResponseConstructorInterface> */
    private array $responseConstructorMap = [];

    /**
     * @param array<int, RequestDecoderInterface>      $requestDecoders
     * @param array<int, DTODataTransformerInterface>  $dtoDataTransformers
     * @param array<int, DTOConstructorInterface>      $dtoConstructors
     * @param array<int, DTOValidatorInterface>        $dtoValidators
     * @param array<int, HandlerWrapperInterface>      $handlerWrappers
     * @param array<int, CommandHandlerInterface>      $commandHandlers
     * @param array<int, QueryHandlerInterface>        $queryHandlers
     * @param array<int, ResponseConstructorInterface> $responseConstructors
     * @param array<int, DTODataTransformerInterface>  $defaultDTODataTransformers
     * @param array<int, DTOValidatorInterface>        $defaultDTOValidators
     * @param array<int, HandlerWrapperInterface>      $defaultHandlerWrappers
     */
    public function __construct(
        iterable $requestDecoders = [],
        iterable $dtoDataTransformers = [],
        iterable $dtoConstructors = [],
        iterable $dtoValidators = [],
        iterable $handlerWrappers = [],
        iterable $commandHandlers = [],
        iterable $queryHandlers = [],
        iterable $responseConstructors = [],
    ) {
        foreach ($requestDecoders as $requestDecoder) {
            $this->requestDecoderMap[get_class($requestDecoder)] = $requestDecoder;
        }
        foreach ($dtoDataTransformers as $dtoDataTransformer) {
            $this->dtoDataTransformerMap[get_class($dtoDataTransformer)] = $dtoDataTransformer;
        }
        foreach ($dtoConstructors as $dtoConstructor) {
            $this->dtoConstructorMap[get_class($dtoConstructor)] = $dtoConstructor;
        }
        foreach ($dtoValidators as $dtoValidator) {
            $this->dtoValidatorMap[get_class($dtoValidator)] = $dtoValidator;
        }
        foreach ($handlerWrappers as $handlerWrapper) {
            $this->handlerWrapperMap[get_class($handlerWrapper)] = $handlerWrapper;
        }
        foreach ($commandHandlers as $commandHandler) {
            $this->commandHandlerMap[get_class($commandHandler)] = $commandHandler;
        }
        foreach ($queryHandlers as $queryHandler) {
            $this->queryHandlerMap[get_class($queryHandler)] = $queryHandler;
        }
        foreach ($responseConstructors as $responseConstructor) {
            $this->responseConstructorMap[get_class($responseConstructor)] = $responseConstructor;
        }
    }

    /**
     * @param class-string<RequestDecoderInterface>|null $requestDecoderClass
     * @param class-string<RequestDecoderInterface>|null $defaultRequestDecoderClass
     */
    public function getRequestDecoder(?string $requestDecoderClass, ?string $defaultRequestDecoderClass): RequestDecoderInterface
    {
        if ($requestDecoderClass !== null) {
            return $this->requestDecoderMap[$requestDecoderClass]
                ?? throw new ConfiguredRequestDecoderNotAvailable($requestDecoderClass);
        }

        if ($defaultRequestDecoderClass !== null) {
            return $this->requestDecoderMap[$defaultRequestDecoderClass]
                ?? throw new ConfiguredRequestDecoderNotAvailable($defaultRequestDecoderClass);
        }

        throw new RequestDecoderOrDefaultRequestDecoderMustBeConfigured();
    }

    /**
     * @param array<array-key, class-string<DTODataTransformerInterface>>|null $dtoDataTransformerClasses
     * @param array<array-key, class-string<DTODataTransformerInterface>>|null $defaultDTODataTransformerClasses
     *
     * @return array<array-key, DTODataTransformerInterface>
     */
    public function getDTODataTransformers(?array $dtoDataTransformerClasses, ?array $defaultDTODataTransformerClasses): array
    {
        if ($dtoDataTransformerClasses === null) {
            if ($defaultDTODataTransformerClasses === null) {
                return [];
            }

            return array_map(
                fn (string $dtoDataTransformerClass) => $this->dtoDataTransformerMap[$dtoDataTransformerClass]
                    ?? throw new ConfiguredDTODataTransformerNotAvailable($dtoDataTransformerClass),
                $defaultDTODataTransformerClasses,
            );
        }

        return array_map(
            fn (string $dtoDataTransformerClass) => $this->dtoDataTransformerMap[$dtoDataTransformerClass]
                ?? throw new ConfiguredDTODataTransformerNotAvailable($dtoDataTransformerClass),
            $dtoDataTransformerClasses,
        );
    }

    /**
     * @param class-string<DTOConstructorInterface>|null $dtoConstructorClass
     * @param class-string<DTOConstructorInterface>|null $defaultDTOConstructorClass
     */
    public function getDTOConstructor(?string $dtoConstructorClass, ?string $defaultDTOConstructorClass): DTOConstructorInterface
    {
        if ($dtoConstructorClass !== null) {
            return $this->dtoConstructorMap[$dtoConstructorClass]
                ?? throw new ConfiguredDTOConstructorNotAvailable($dtoConstructorClass);
        }

        if ($defaultDTOConstructorClass !== null) {
            return $this->dtoConstructorMap[$defaultDTOConstructorClass]
                ?? throw new ConfiguredDTOConstructorNotAvailable($defaultDTOConstructorClass);
        }

        throw new NoDefaultDTOConstructorDefined();
    }

    /**
     * @param array<array-key, class-string<DTOValidatorInterface>>|null $dtoValidatorClasses
     * @param array<array-key, class-string<DTOValidatorInterface>>|null $defaultDTOValidatorClasses
     *
     * @return array<array-key, DTOValidatorInterface>
     */
    public function getDTOValidators(?array $dtoValidatorClasses, ?array $defaultDTOValidatorClasses): array
    {
        if ($dtoValidatorClasses === null) {
            if ($defaultDTOValidatorClasses === null) {
                return [];
            }

            return array_map(
                fn (string $dtoValidatorClass) => $this->dtoValidatorMap[$dtoValidatorClass]
                    ?? throw new ConfiguredDTOValidatorNotAvailable($dtoValidatorClass),
                $defaultDTOValidatorClasses,
            );
        }

        return array_map(
            fn (string $dtoValidatorClass) => $this->dtoValidatorMap[$dtoValidatorClass]
                ?? throw new ConfiguredDTOValidatorNotAvailable($dtoValidatorClass),
            $dtoValidatorClasses,
        );
    }

    /**
     * @param array<int, HandlerWrapperConfiguration>|null           $handlerWrapperConfigurations
     * @param array<int, class-string<HandlerWrapperInterface>>|null $defaultHandlerWrapperClasses
     *
     * @return array<array-key, HandlerWrapperWithParameters>
     */
    public function getHandlerWrappersWithParameters(?array $handlerWrapperConfigurations, ?array $defaultHandlerWrapperClasses): array
    {
        if ($handlerWrapperConfigurations === null) {
            if ($defaultHandlerWrapperClasses === null) {
                return [];
            }

            return array_map(
                fn (string $handlerWrapperClass) => new HandlerWrapperWithParameters(
                    $this->handlerWrapperMap[$handlerWrapperClass]
                        ?? throw new ConfiguredHandlerWrapperNotAvailable($handlerWrapperClass),
                    null,
                ),
                $defaultHandlerWrapperClasses,
            );
        }

        return array_map(
            fn (HandlerWrapperConfiguration $handlerWrapperConfiguration) => new HandlerWrapperWithParameters(
                $this->handlerWrapperMap[$handlerWrapperConfiguration->handlerWrapperClass]
                ?? throw new ConfiguredHandlerWrapperNotAvailable($handlerWrapperConfiguration->handlerWrapperClass),
                $handlerWrapperConfiguration->parameters,
            ),
            $handlerWrapperConfigurations,
        );
    }

    /** @param class-string<CommandHandlerInterface> $handlerClass */
    public function getCommandHandler(string $handlerClass): CommandHandlerInterface
    {
        return $this->commandHandlerMap[$handlerClass]
            ?? throw new ConfiguredCommandHandlerNotAvailable($handlerClass);
    }

    /** @param class-string<QueryHandlerInterface> $handlerClass */
    public function getQueryHandler(string $handlerClass): QueryHandlerInterface
    {
        return $this->queryHandlerMap[$handlerClass]
            ?? throw new ConfiguredQueryHandlerNotAvailable($handlerClass);
    }

    /** @param class-string<ResponseConstructorInterface> $responseConstructorClass */
    public function getResponseConstructor(
        ?string $responseConstructorClass,
        ?string $defaultResponseConstructorClass,
    ): ResponseConstructorInterface {
        if ($responseConstructorClass !== null) {
            return $this->responseConstructorMap[$responseConstructorClass]
                ?? throw new ConfiguredResponseConstructorNotAvailable($responseConstructorClass);
        }

        if ($defaultResponseConstructorClass !== null) {
            return $this->responseConstructorMap[$defaultResponseConstructorClass]
                ?? throw new ConfiguredResponseConstructorNotAvailable($defaultResponseConstructorClass);
        }

        throw new NoDefaultResponseConstructorDefined();
    }
}
