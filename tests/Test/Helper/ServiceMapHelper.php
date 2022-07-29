<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Helper;

use DigitalCraftsman\CQRS\Command\CommandHandlerInterface;
use DigitalCraftsman\CQRS\DTOConstructor\DTOConstructorInterface;
use DigitalCraftsman\CQRS\DTODataTransformer\DTODataTransformerInterface;
use DigitalCraftsman\CQRS\DTOValidator\DTOValidatorInterface;
use DigitalCraftsman\CQRS\HandlerWrapper\HandlerWrapperInterface;
use DigitalCraftsman\CQRS\Query\QueryHandlerInterface;
use DigitalCraftsman\CQRS\RequestDecoder\RequestDecoderInterface;
use DigitalCraftsman\CQRS\ResponseConstructor\ResponseConstructorInterface;
use DigitalCraftsman\CQRS\ServiceMap\ServiceMap;
use DigitalCraftsman\CQRS\Test\Utility\ServiceLocatorSimulator;

final class ServiceMapHelper
{
    /**
     * @param array<int, RequestDecoderInterface>|null      $requestDecoders
     * @param array<int, DTODataTransformerInterface>|null  $dtoDataTransformers
     * @param array<int, DTOConstructorInterface>|null      $dtoConstructors
     * @param array<int, DTOValidatorInterface>|null        $dtoValidators
     * @param array<int, HandlerWrapperInterface>|null      $handlerWrappers
     * @param array<int, CommandHandlerInterface>|null      $commandHandlers
     * @param array<int, QueryHandlerInterface>|null        $queryHandlers
     * @param array<int, ResponseConstructorInterface>|null $responseConstructors
     */
    public static function serviceMap(
        ?array $requestDecoders = null,
        ?array $dtoDataTransformers = null,
        ?array $dtoConstructors = null,
        ?array $dtoValidators = null,
        ?array $handlerWrappers = null,
        ?array $commandHandlers = null,
        ?array $queryHandlers = null,
        ?array $responseConstructors = null,
    ): ServiceMap {
        $requestDecodersMap = [];
        foreach ($requestDecoders ?? [] as $requestDecoder) {
            $requestDecodersMap[$requestDecoder::class] = $requestDecoder;
        }

        $dtoDataTransformersMap = [];
        foreach ($dtoDataTransformers ?? [] as $dtoDataTransformer) {
            $dtoDataTransformersMap[$dtoDataTransformer::class] = $dtoDataTransformer;
        }

        $dtoConstructorsMap = [];
        foreach ($dtoConstructors ?? [] as $dtoConstructor) {
            $dtoConstructorsMap[$dtoConstructor::class] = $dtoConstructor;
        }

        $dtoValidatorsMap = [];
        foreach ($dtoValidators ?? [] as $dtoValidator) {
            $dtoValidatorsMap[$dtoValidator::class] = $dtoValidator;
        }

        $handlerWrappersMap = [];
        foreach ($handlerWrappers ?? [] as $handlerWrapper) {
            $handlerWrappersMap[$handlerWrapper::class] = $handlerWrapper;
        }

        $commandHandlersMap = [];
        foreach ($commandHandlers ?? [] as $commandHandler) {
            $commandHandlersMap[$commandHandler::class] = $commandHandler;
        }

        $queryHandlersMap = [];
        foreach ($queryHandlers ?? [] as $queryHandler) {
            $queryHandlersMap[$queryHandler::class] = $queryHandler;
        }

        $responseConstructorsMap = [];
        foreach ($responseConstructors ?? [] as $responseConstructor) {
            $responseConstructorsMap[$responseConstructor::class] = $responseConstructor;
        }

        return new ServiceMap(
            requestDecoders: new ServiceLocatorSimulator($requestDecodersMap),
            dtoDataTransformers: new ServiceLocatorSimulator($dtoDataTransformersMap),
            dtoConstructors: new ServiceLocatorSimulator($dtoConstructorsMap),
            dtoValidators: new ServiceLocatorSimulator($dtoValidatorsMap),
            handlerWrappers: new ServiceLocatorSimulator($handlerWrappersMap),
            commandHandlers: new ServiceLocatorSimulator($commandHandlersMap),
            queryHandlers: new ServiceLocatorSimulator($queryHandlersMap),
            responseConstructors: new ServiceLocatorSimulator($responseConstructorsMap),
        );
    }
}
