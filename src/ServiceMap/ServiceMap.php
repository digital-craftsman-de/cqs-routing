<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ServiceMap;

use DigitalCraftsman\CQRS\Command\CommandHandlerInterface;
use DigitalCraftsman\CQRS\DTO\Configuration;
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
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredWorkflowHandlerNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\NoDefaultDTOConstructorDefined;
use DigitalCraftsman\CQRS\ServiceMap\Exception\NoDefaultRequestDecoderDefined;
use DigitalCraftsman\CQRS\ServiceMap\Exception\NoDefaultResponseConstructorDefined;
use DigitalCraftsman\CQRS\Workflow\WorkflowHandlerInterface;

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

    /** @var array<string, WorkflowHandlerInterface> */
    private array $workflowHandlerMap = [];

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
     * @param array<int, WorkflowHandlerInterface>     $workflowHandlers
     * @param array<int, ResponseConstructorInterface> $responseConstructors
     * @param array<int, DTODataTransformerInterface>  $defaultDTODataTransformers
     * @param array<int, DTOValidatorInterface>        $defaultDTOValidators
     * @param array<int, HandlerWrapperInterface>      $defaultHandlerWrappers
     */
    public function __construct(
        iterable $requestDecoders,
        iterable $dtoDataTransformers,
        iterable $dtoConstructors,
        iterable $dtoValidators,
        iterable $handlerWrappers,
        iterable $commandHandlers,
        iterable $queryHandlers,
        iterable $workflowHandlers,
        iterable $responseConstructors,
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
        foreach ($workflowHandlers as $workflowHandler) {
            $this->workflowHandlerMap[get_class($workflowHandler)] = $workflowHandler;
        }
        foreach ($responseConstructors as $responseConstructor) {
            $this->responseConstructorMap[get_class($responseConstructor)] = $responseConstructor;
        }
    }

    public function getRequestDecoder(Configuration $configuration, ?string $defaultRequestDecoderClass): RequestDecoderInterface
    {
        if ($configuration->requestDecoderClass !== null) {
            return $this->requestDecoderMap[$configuration->requestDecoderClass]
                ?? throw new ConfiguredRequestDecoderNotAvailable($configuration->requestDecoderClass);
        }

        if ($defaultRequestDecoderClass !== null) {
            return $this->requestDecoderMap[$defaultRequestDecoderClass]
                ?? throw new ConfiguredRequestDecoderNotAvailable($defaultRequestDecoderClass);
        }

        throw new NoDefaultRequestDecoderDefined();
    }

    /** @return array<array-key, DTODataTransformerInterface> */
    public function getDTODataTransformers(Configuration $configuration, ?array $defaultDTODataTransformerClasses): array
    {
        if ($configuration->dtoDataTransformerClasses === null) {
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
            $configuration->dtoDataTransformerClasses,
        );
    }

    public function getDTOConstructor(Configuration $configuration, ?string $defaultDTOConstructorClass): DTOConstructorInterface
    {
        if ($configuration->dtoConstructorClass !== null) {
            return $this->dtoConstructorMap[$configuration->dtoConstructorClass]
                ?? throw new ConfiguredDTOConstructorNotAvailable($configuration->dtoConstructorClass);
        }

        if ($defaultDTOConstructorClass !== null) {
            return $this->dtoConstructorMap[$defaultDTOConstructorClass]
                ?? throw new ConfiguredDTOConstructorNotAvailable($defaultDTOConstructorClass);
        }

        throw new NoDefaultDTOConstructorDefined();
    }

    /** @return array<array-key, DTOValidatorInterface> */
    public function getDTOValidators(Configuration $configuration, ?array $defaultDTOValidatorClasses): array
    {
        if ($configuration->dtoValidatorClasses === null) {
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
            $configuration->dtoValidatorClasses,
        );
    }

    /** @return array<array-key, HandlerWrapperWithParameters> */
    public function getHandlerWrappersWithParameters(Configuration $configuration, ?array $defaultHandlerWrapperClasses): array
    {
        if ($configuration->handlerWrapperConfigurations === null) {
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
            $configuration->handlerWrapperConfigurations,
        );
    }

    public function getCommandHandler(Configuration $configuration): CommandHandlerInterface
    {
        return $this->commandHandlerMap[$configuration->handlerClass]
            ?? throw new ConfiguredCommandHandlerNotAvailable($configuration->handlerClass);
    }

    public function getQueryHandler(Configuration $configuration): QueryHandlerInterface
    {
        return $this->queryHandlerMap[$configuration->handlerClass]
            ?? throw new ConfiguredQueryHandlerNotAvailable($configuration->handlerClass);
    }

    public function getWorkflowHandler(Configuration $configuration): WorkflowHandlerInterface
    {
        return $this->workflowHandlerMap[$configuration->handlerClass]
            ?? throw new ConfiguredWorkflowHandlerNotAvailable($configuration->handlerClass);
    }

    public function getResponseConstructor(
        Configuration $configuration,
        ?string $defaultResponseConstructorClass,
    ): ResponseConstructorInterface {
        if ($configuration->responseConstructorClass !== null) {
            return $this->responseConstructorMap[$configuration->responseConstructorClass]
                ?? throw new ConfiguredResponseConstructorNotAvailable($configuration->responseConstructorClass);
        }

        if ($defaultResponseConstructorClass !== null) {
            return $this->responseConstructorMap[$defaultResponseConstructorClass]
                ?? throw new ConfiguredResponseConstructorNotAvailable($defaultResponseConstructorClass);
        }

        throw new NoDefaultResponseConstructorDefined();
    }
}
