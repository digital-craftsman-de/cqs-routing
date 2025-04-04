<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Controller;

use DigitalCraftsman\CQSRouting\HandlerWrapper\DTO\HandlerWrapperStep;
use DigitalCraftsman\CQSRouting\Query\Query;
use DigitalCraftsman\CQSRouting\Routing\RouteConfigurationBuilder;
use DigitalCraftsman\CQSRouting\Routing\RoutePayload;
use DigitalCraftsman\CQSRouting\ServiceMap\ServiceMap;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @psalm-import-type NormalizedConfigurationParameters from RoutePayload
 */
final class QueryController extends AbstractController
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
        $configuration = $this->routeConfigurationBuilder->buildConfigurationForQuery(
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
            $requestData = $requestDataTransformer->transformRequestData($configuration->dtoClass, $requestData, $parameters);
        }

        // -- Construct query from request data
        $dtoConstructor = $this->serviceMap->getDTOConstructor($configuration->dtoConstructorClass);

        /**
         * @var Query $query
         */
        $query = $dtoConstructor->constructDTO(
            $requestData,
            $configuration->dtoClass,
        );

        // -- Validate query
        foreach ($configuration->dtoValidatorClasses as $dtoValidatorClass => $parameters) {
            $dtoValidator = $this->serviceMap->getDTOValidator($dtoValidatorClass);
            $dtoValidator->validateDTO($request, $query, $parameters);
        }

        // -- Wrap handlers
        /**
         * The wrapper handlers are quite complex, so additional explanation can be found in @HandlerWrapperStep.
         */
        $handlerWrapperClassesForPrepareStep = HandlerWrapperStep::prepare($configuration->handlerWrapperClasses);
        foreach ($handlerWrapperClassesForPrepareStep->orderedHandlerWrapperClasses as $handlerWrapperClass => $parameters) {
            $handlerWrapper = $this->serviceMap->getHandlerWrapper($handlerWrapperClass);
            $handlerWrapper->prepare(
                $query,
                $request,
                $parameters,
            );
        }

        // -- Trigger query through query handler
        /**
         * @psalm-suppress PossiblyInvalidArgument
         */
        $queryHandler = $this->serviceMap->getQueryHandler($configuration->handlerClass);

        $result = null;

        try {
            /**
             * @psalm-suppress InvalidFunctionCall
             */
            $result = $queryHandler($query);

            $handlerWrapperClassesForThenStep = HandlerWrapperStep::then($configuration->handlerWrapperClasses);
            foreach ($handlerWrapperClassesForThenStep->orderedHandlerWrapperClasses as $handlerWrapperClass => $parameters) {
                $handlerWrapper = $this->serviceMap->getHandlerWrapper($handlerWrapperClass);
                $handlerWrapper->then(
                    $query,
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
                        $query,
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

        return $responseConstructor->constructResponse($result, $request);
    }
}
