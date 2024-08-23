<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test\Helper;

use DigitalCraftsman\CQSRouting\Command\CommandHandlerInterface;
use DigitalCraftsman\CQSRouting\DTOConstructor\DTOConstructorInterface;
use DigitalCraftsman\CQSRouting\DTOValidator\DTOValidatorInterface;
use DigitalCraftsman\CQSRouting\HandlerWrapper\HandlerWrapperInterface;
use DigitalCraftsman\CQSRouting\Query\QueryHandlerInterface;
use DigitalCraftsman\CQSRouting\RequestDataTransformer\RequestDataTransformerInterface;
use DigitalCraftsman\CQSRouting\RequestDecoder\RequestDecoderInterface;
use DigitalCraftsman\CQSRouting\RequestValidator\RequestValidatorInterface;
use DigitalCraftsman\CQSRouting\ResponseConstructor\ResponseConstructorInterface;
use DigitalCraftsman\CQSRouting\ServiceMap\ServiceMap;
use DigitalCraftsman\CQSRouting\Test\Utility\ServiceLocatorSimulator;

final readonly class ServiceMapHelper
{
    /**
     * @param array<int, RequestValidatorInterface>|null       $requestValidators
     * @param array<int, RequestDecoderInterface>|null         $requestDecoders
     * @param array<int, RequestDataTransformerInterface>|null $requestDataTransformers
     * @param array<int, DTOConstructorInterface>|null         $dtoConstructors
     * @param array<int, DTOValidatorInterface>|null           $dtoValidators
     * @param array<int, HandlerWrapperInterface>|null         $handlerWrappers
     * @param array<int, CommandHandlerInterface>|null         $commandHandlers
     * @param array<int, QueryHandlerInterface>|null           $queryHandlers
     * @param array<int, ResponseConstructorInterface>|null    $responseConstructors
     */
    public static function serviceMap(
        ?array $requestValidators = null,
        ?array $requestDecoders = null,
        ?array $requestDataTransformers = null,
        ?array $dtoConstructors = null,
        ?array $dtoValidators = null,
        ?array $handlerWrappers = null,
        ?array $commandHandlers = null,
        ?array $queryHandlers = null,
        ?array $responseConstructors = null,
    ): ServiceMap {
        $requestValidatorsMap = [];
        foreach ($requestValidators ?? [] as $requestValidator) {
            $requestValidatorsMap[$requestValidator::class] = $requestValidator;
        }

        $requestDecodersMap = [];
        foreach ($requestDecoders ?? [] as $requestDecoder) {
            $requestDecodersMap[$requestDecoder::class] = $requestDecoder;
        }

        $requestDataTransformersMap = [];
        foreach ($requestDataTransformers ?? [] as $requestDataTransformer) {
            $requestDataTransformersMap[$requestDataTransformer::class] = $requestDataTransformer;
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
            requestValidators: new ServiceLocatorSimulator($requestValidatorsMap),
            requestDecoders: new ServiceLocatorSimulator($requestDecodersMap),
            requestDataTransformers: new ServiceLocatorSimulator($requestDataTransformersMap),
            dtoConstructors: new ServiceLocatorSimulator($dtoConstructorsMap),
            dtoValidators: new ServiceLocatorSimulator($dtoValidatorsMap),
            handlerWrappers: new ServiceLocatorSimulator($handlerWrappersMap),
            commandHandlers: new ServiceLocatorSimulator($commandHandlersMap),
            queryHandlers: new ServiceLocatorSimulator($queryHandlersMap),
            responseConstructors: new ServiceLocatorSimulator($responseConstructorsMap),
        );
    }
}
