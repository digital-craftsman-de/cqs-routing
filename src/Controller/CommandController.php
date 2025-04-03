<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Controller;

use DigitalCraftsman\CQSRouting\Command\Command;
use DigitalCraftsman\CQSRouting\HandlerWrapper\DTO\HandlerWrapperStep;
use DigitalCraftsman\CQSRouting\Routing\RouteConfigurationBuilder;
use DigitalCraftsman\CQSRouting\Routing\RoutePayload;
use DigitalCraftsman\CQSRouting\ServiceMap\ServiceMap;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @psalm-import-type NormalizedConfigurationParameters from RoutePayload
 */
final class CommandController extends AbstractController
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(
        private readonly ServiceMap $serviceMap,
        private readonly RouteConfigurationBuilder $routeConfigurationBuilder,
    ) {
    }

    /**
     * We don't type the $routePayload because we never trigger it manually, it's only supplied through Symfony.
     */
    public function handle(
        Request $request,
        array $routePayload,
    ): Response {
        /**
         * @psalm-suppress MixedArgumentTypeCoercion
         */
        $configuration = $this->routeConfigurationBuilder->buildConfigurationForCommand(
            RoutePayload::fromPayload($routePayload),
        );

        // -- Validate request
        foreach ($configuration->requestValidatorClasses as $requestValidatorClass => $parameters) {
            $requestValidator = $this->serviceMap->getRequestValidator($requestValidatorClass);
            $requestValidator->validateRequest($request, $parameters);
        }

        // -- Get request data from request
        $requestDecoder = $this->serviceMap->getRequestDecoder($configuration->requestDecoderClass);
        $requestData = $requestDecoder->decodeRequest($request);

        // -- Transform request data
        foreach ($configuration->requestDataTransformerClasses as $requestDataTransformerClass => $parameters) {
            $requestDataTransformer = $this->serviceMap->getRequestDataTransformer($requestDataTransformerClass);
            $requestData = $requestDataTransformer->transformRequestData(
                $configuration->dtoClass,
                $requestData,
                $parameters,
            );
        }

        // -- Construct command from request data
        $dtoConstructor = $this->serviceMap->getDTOConstructor($configuration->dtoConstructorClass);

        /**
         * @var Command $command
         */
        $command = $dtoConstructor->constructDTO(
            $requestData,
            $configuration->dtoClass,
        );

        // -- Validate command
        foreach ($configuration->dtoValidatorClasses as $dtoValidatorClass => $parameters) {
            $dtoValidator = $this->serviceMap->getDTOValidator($dtoValidatorClass);
            $dtoValidator->validateDTO($request, $command, $parameters);
        }

        // -- Wrap handlers
        /**
         * The wrapper handlers are quite complex, so additional explanation can be found in @HandlerWrapperStep.
         */
        $handlerWrapperClassesForPrepareStep = HandlerWrapperStep::prepare($configuration->handlerWrapperClasses);
        foreach ($handlerWrapperClassesForPrepareStep->orderedHandlerWrapperClasses as $handlerWrapperClass => $parameters) {
            $handlerWrapper = $this->serviceMap->getHandlerWrapper($handlerWrapperClass);
            $handlerWrapper->prepare(
                $command,
                $request,
                $parameters,
            );
        }

        try {
            // -- Trigger command through command handler
            /**
             * @psalm-suppress PossiblyInvalidArgument
             */
            $commandHandler = $this->serviceMap->getCommandHandler($configuration->handlerClass);
            /**
             * @psalm-suppress InvalidFunctionCall
             */
            $commandHandler($command);

            $handlerWrapperClassesForThenStep = HandlerWrapperStep::then($configuration->handlerWrapperClasses);
            foreach ($handlerWrapperClassesForThenStep->orderedHandlerWrapperClasses as $handlerWrapperClass => $parameters) {
                $handlerWrapper = $this->serviceMap->getHandlerWrapper($handlerWrapperClass);
                $handlerWrapper->then(
                    $command,
                    $request,
                    $parameters,
                );
            }
        } catch (\Exception $exception) {
            // Exception is handled by every handler wrapper until one does not return the exception anymore.
            $exceptionToHandle = $exception;
            $handlerWrapperCatchStep = HandlerWrapperStep::catch($configuration->handlerWrapperClasses);
            foreach ($handlerWrapperCatchStep->orderedHandlerWrapperClasses as $handlerWrapperClass => $parameters) {
                if ($exceptionToHandle !== null) {
                    $handlerWrapper = $this->serviceMap->getHandlerWrapper($handlerWrapperClass);
                    $exceptionToHandle = $handlerWrapper->catch(
                        $command,
                        $request,
                        $parameters,
                        $exceptionToHandle,
                    );
                }
            }

            if ($exceptionToHandle !== null) {
                throw $exceptionToHandle;
            }
        }

        // -- Construct and return response
        $responseConstructor = $this->serviceMap->getResponseConstructor($configuration->responseConstructorClass);

        return $responseConstructor->constructResponse(null, $request);
    }
}
