<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Controller;

use DigitalCraftsman\CQSRouting\DTOConstructor\DTOConstructorInterface;
use DigitalCraftsman\CQSRouting\DTOValidator\DTOValidatorInterface;
use DigitalCraftsman\CQSRouting\HandlerWrapper\DTO\HandlerWrapperStep;
use DigitalCraftsman\CQSRouting\HandlerWrapper\HandlerWrapperInterface;
use DigitalCraftsman\CQSRouting\Query\Query;
use DigitalCraftsman\CQSRouting\RequestDataTransformer\RequestDataTransformerInterface;
use DigitalCraftsman\CQSRouting\RequestDecoder\RequestDecoderInterface;
use DigitalCraftsman\CQSRouting\RequestValidator\RequestValidatorInterface;
use DigitalCraftsman\CQSRouting\ResponseConstructor\ResponseConstructorInterface;
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
     * @param array<class-string<RequestValidatorInterface>, NormalizedConfigurationParameters>|null       $defaultRequestValidatorClasses
     * @param class-string<RequestDecoderInterface>|null                                                   $defaultRequestDecoderClass
     * @param array<class-string<RequestDataTransformerInterface>, NormalizedConfigurationParameters>|null $defaultRequestDataTransformerClasses
     * @param class-string<DTOConstructorInterface>|null                                                   $defaultDTOConstructorClass
     * @param array<class-string<DTOValidatorInterface>, NormalizedConfigurationParameters>|null           $defaultDTOValidatorClasses
     * @param array<class-string<HandlerWrapperInterface>, NormalizedConfigurationParameters>|null         $defaultHandlerWrapperClasses
     * @param class-string<ResponseConstructorInterface>|null                                              $defaultResponseConstructorClass
     *
     * @codeCoverageIgnore
     */
    public function __construct(
        private readonly ServiceMap $serviceMap,
        private readonly ?array $defaultRequestValidatorClasses,
        private readonly ?string $defaultRequestDecoderClass,
        private readonly ?array $defaultRequestDataTransformerClasses,
        private readonly ?string $defaultDTOConstructorClass,
        private readonly ?array $defaultDTOValidatorClasses,
        private readonly ?array $defaultHandlerWrapperClasses,
        private readonly ?string $defaultResponseConstructorClass,
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
        $configuration = RoutePayload::fromPayload($routePayload);

        // -- Validate request
        $requestValidatorClasses = RoutePayload::mergeRequestValidatorClassesFromRouteWithDefaults(
            $configuration->requestValidatorClasses,
            $configuration->requestValidatorClassesToMergeWithDefault,
            $this->defaultRequestValidatorClasses,
        );
        foreach ($requestValidatorClasses as $requestValidatorClass => $parameters) {
            $requestValidator = $this->serviceMap->getRequestValidator($requestValidatorClass);
            $requestValidator->validateRequest($request, $parameters);
        }

        // -- Get request data from request
        $requestDecoder = $this->serviceMap->getRequestDecoder(
            $configuration->requestDecoderClass,
            $this->defaultRequestDecoderClass,
        );
        $requestData = $requestDecoder->decodeRequest($request);

        // -- Transform request data
        $requestDataTransformerClasses = RoutePayload::mergeRequestDataTransformerClassesFromRouteWithDefaults(
            $configuration->requestDataTransformerClasses,
            $configuration->requestDataTransformerClassesToMergeWithDefault,
            $this->defaultRequestDataTransformerClasses,
        );
        foreach ($requestDataTransformerClasses as $requestDataTransformerClass => $parameters) {
            $requestDataTransformer = $this->serviceMap->getRequestDataTransformer($requestDataTransformerClass);
            $requestData = $requestDataTransformer->transformRequestData($configuration->dtoClass, $requestData, $parameters);
        }

        // -- Construct query from request data
        $dtoConstructor = $this->serviceMap->getDTOConstructor(
            $configuration->dtoConstructorClass,
            $this->defaultDTOConstructorClass,
        );

        /**
         * @var Query $query
         */
        $query = $dtoConstructor->constructDTO($requestData, $configuration->dtoClass);

        // -- Validate query
        $dtoValidatorClasses = RoutePayload::mergeDTOValidatorClassesFromRouteWithDefaults(
            $configuration->dtoValidatorClasses,
            $configuration->dtoValidatorClassesToMergeWithDefault,
            $this->defaultDTOValidatorClasses,
        );
        foreach ($dtoValidatorClasses as $dtoValidatorClass => $parameters) {
            $dtoValidator = $this->serviceMap->getDTOValidator($dtoValidatorClass);
            $dtoValidator->validateDTO($request, $query, $parameters);
        }

        // -- Wrap handlers
        /**
         * The wrapper handlers are quite complex, so additional explanation can be found in @HandlerWrapperStep.
         */
        $handlerWrapperClasses = RoutePayload::mergeHandlerWrapperClassesFromRouteWithDefaults(
            $configuration->handlerWrapperClasses,
            $configuration->handlerWrapperClassesToMergeWithDefault,
            $this->defaultHandlerWrapperClasses,
        );

        $handlerWrapperClassesForPrepareStep = HandlerWrapperStep::prepare($handlerWrapperClasses);
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

            $handlerWrapperClassesForThenStep = HandlerWrapperStep::then($handlerWrapperClasses);
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
            $handlerWrapperCatchStep = HandlerWrapperStep::catch($handlerWrapperClasses);
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
        $responseConstructor = $this->serviceMap->getResponseConstructor(
            $configuration->responseConstructorClass,
            $this->defaultResponseConstructorClass,
        );

        return $responseConstructor->constructResponse($result, $request);
    }
}
