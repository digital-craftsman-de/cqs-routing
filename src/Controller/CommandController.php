<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Controller;

use DigitalCraftsman\CQRS\Command\Command;
use DigitalCraftsman\CQRS\Command\CommandHandlerInterface;
use DigitalCraftsman\CQRS\DTO\Configuration;
use DigitalCraftsman\CQRS\DTO\HandlerWrapperConfiguration;
use DigitalCraftsman\CQRS\DTOConstructor\DTOConstructorInterface;
use DigitalCraftsman\CQRS\DTODataTransformer\DTODataTransformerInterface;
use DigitalCraftsman\CQRS\DTOValidator\DTOValidatorInterface;
use DigitalCraftsman\CQRS\HandlerWrapper\DTO\HandlerWrapperStep;
use DigitalCraftsman\CQRS\HandlerWrapper\DTO\HandlerWrapperWithParameters;
use DigitalCraftsman\CQRS\HandlerWrapper\HandlerWrapperInterface;
use DigitalCraftsman\CQRS\RequestDecoder\RequestDecoderInterface;
use DigitalCraftsman\CQRS\ResponseConstructor\ResponseConstructorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CommandController extends AbstractController
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

    /** @var array<string, ResponseConstructorInterface> */
    private array $responseConstructorMap = [];

    /**
     * @param array<int, RequestDecoderInterface>      $requestDecoders
     * @param array<int, DTODataTransformerInterface>  $dtoDataTransformers
     * @param array<int, DTOConstructorInterface>      $dtoConstructors
     * @param array<int, DTOValidatorInterface>        $dtoValidators
     * @param array<int, HandlerWrapperInterface>      $handlerWrappers
     * @param array<int, CommandHandlerInterface>      $commandHandlers
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
        iterable $responseConstructors,
        private ?RequestDecoderInterface $defaultRequestDecoder = null,
        private array $defaultDTODataTransformers = [],
        private ?DTOConstructorInterface $defaultDTOConstructor = null,
        private array $defaultDTOValidators = [],
        private array $defaultHandlerWrappers = [],
        private ?ResponseConstructorInterface $defaultResponseConstructor = null,
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
        foreach ($responseConstructors as $responseConstructor) {
            $this->responseConstructorMap[get_class($responseConstructor)] = $responseConstructor;
        }
    }

    /** We don't type the $routePayload because we never trigger it manually, it's only supplied through Symfony. */
    public function handle(
        Request $request,
        array $routePayload,
    ): Response {
        /** @psalm-suppress MixedArgumentTypeCoercion */
        $configuration = Configuration::fromRoutePayload($routePayload);

        // Get data from request
        $requestDecoder = $this->getRequestDecoder($configuration);
        $commandData = $requestDecoder->decodeRequest($request);

        // Transform data
        $dtoDataTransformers = $this->getDTODataTransformers($configuration);
        foreach ($dtoDataTransformers as $dtoDataTransformer) {
            $commandData = $dtoDataTransformer->transformDTOData($commandData);
        }

        // Construct command from data
        $dtoConstructor = $this->getDTOConstructor($configuration);

        /** @var Command $command */
        $command = $dtoConstructor->constructDTO($commandData, $configuration->dtoClass);

        // Validate command
        $dtoValidators = $this->getDTOValidators($configuration);
        foreach ($dtoValidators as $dtoValidator) {
            $dtoValidator->validateDTO($request, $command);
        }

        // Wrap handlers
        /** The wrapper handlers are quite complex, so additional explanation can be found in @HandlerWrapperStep */
        $handlerWrappersWithParameters = $this->getHandlerWrappersWithParameters($configuration);

        $handlerWrapperPrepareStep = HandlerWrapperStep::prepare($handlerWrappersWithParameters);
        foreach ($handlerWrapperPrepareStep->orderedHandlerWrappersWithParameters as $handlerWrapperWithParameters) {
            $handlerWrapperWithParameters->handlerWrapper->prepare(
                $command,
                $handlerWrapperWithParameters->parameters,
            );
        }

        try {
            // Trigger command through command handler
            $commandHandler = $this->getCommandHandler($configuration);
            $commandHandler->handle($command);

            $handlerWrapperThenStep = HandlerWrapperStep::then($handlerWrappersWithParameters);
            foreach ($handlerWrapperThenStep->orderedHandlerWrappersWithParameters as $handlerWrapperWithParameters) {
                $handlerWrapperWithParameters->handlerWrapper->then(
                    $command,
                    $handlerWrapperWithParameters->parameters,
                );
            }
        } catch (\Exception $exception) {
            // Exception is handled by every handler wrapper until one does not return the exception anymore.
            $exceptionToHandle = $exception;
            $handlerWrapperCatchStep = HandlerWrapperStep::catch($handlerWrappersWithParameters);
            foreach ($handlerWrapperCatchStep->orderedHandlerWrappersWithParameters as $handlerWrapperWithParameters) {
                if ($exceptionToHandle === null) {
                    continue;
                }

                /**
                 * Psalm seems to think it's in the try block because of the catch.
                 *
                 * @psalm-suppress PossiblyUndefinedVariable
                 */
                $exceptionToHandle = $handlerWrapperWithParameters->handlerWrapper->catch(
                    $command,
                    $handlerWrapperWithParameters->parameters,
                    $exceptionToHandle,
                );
            }

            if ($exceptionToHandle !== null) {
                throw $exceptionToHandle;
            }
        } finally {
            $handlerWrapperFinallyStep = HandlerWrapperStep::finally($handlerWrappersWithParameters);
            foreach ($handlerWrapperFinallyStep->orderedHandlerWrappersWithParameters as $handlerWrapperWithParameters) {
                $handlerWrapperWithParameters->handlerWrapper->finally(
                    $command,
                    $handlerWrapperWithParameters->parameters,
                );
            }
        }

        // Construct and return response
        $responseConstructor = $this->getResponseConstructor($configuration);

        return $responseConstructor->constructResponse(null);
    }

    private function getRequestDecoder(Configuration $configuration): RequestDecoderInterface
    {
        return $configuration->requestDecoderClass !== null
            ? $this->requestDecoderMap[$configuration->requestDecoderClass]
            : $this->defaultRequestDecoder;
    }

    /** @return array<array-key, DTODataTransformerInterface> */
    private function getDTODataTransformers(Configuration $configuration): array
    {
        if ($configuration->dtoDataTransformerClasses === null) {
            return $this->defaultDTODataTransformers;
        }

        return array_map(
            fn (string $dtoDataTransformerClass) => $this->dtoDataTransformerMap[$dtoDataTransformerClass],
            $configuration->dtoDataTransformerClasses,
        );
    }

    private function getDTOConstructor(Configuration $configuration): DTOConstructorInterface
    {
        return $configuration->dtoConstructorClass !== null
            ? $this->dtoConstructorMap[$configuration->dtoConstructorClass]
            : $this->defaultDTOConstructor;
    }

    /** @return array<array-key, DTOValidatorInterface> */
    private function getDTOValidators(Configuration $configuration): array
    {
        if ($configuration->dtoValidatorClasses === null) {
            return $this->defaultDTOValidators;
        }

        return array_map(
            fn (string $dtoValidatorClass) => $this->dtoValidatorMap[$dtoValidatorClass],
            $configuration->dtoValidatorClasses,
        );
    }

    /** @return array<array-key, HandlerWrapperWithParameters> */
    private function getHandlerWrappersWithParameters(Configuration $configuration): array
    {
        if ($configuration->handlerWrapperConfigurations === null) {
            return array_map(
                static fn (HandlerWrapperInterface $handlerWrapper) => new HandlerWrapperWithParameters(
                    $handlerWrapper,
                    null,
                ),
                $this->defaultHandlerWrappers,
            );
        }

        return array_map(
            fn (HandlerWrapperConfiguration $handlerWrapperConfiguration) => new HandlerWrapperWithParameters(
                $this->handlerWrapperMap[$handlerWrapperConfiguration->handlerWrapperClass],
                $handlerWrapperConfiguration->parameters,
            ),
            $configuration->handlerWrapperConfigurations,
        );
    }

    private function getCommandHandler(Configuration $configuration): CommandHandlerInterface
    {
        return $this->commandHandlerMap[$configuration->handlerClass];
    }

    private function getResponseConstructor(Configuration $configuration): ResponseConstructorInterface
    {
        return $configuration->responseConstructorClass !== null
            ? $this->responseConstructorMap[$configuration->responseConstructorClass]
            : $this->defaultResponseConstructor;
    }
}
