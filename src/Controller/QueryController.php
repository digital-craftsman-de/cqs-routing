<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Controller;

use DigitalCraftsman\CQRS\DTOConstructor\DTOConstructorInterface;
use DigitalCraftsman\CQRS\DTOValidator\DTOValidatorInterface;
use DigitalCraftsman\CQRS\HandlerWrapper\DTO\HandlerWrapperStep;
use DigitalCraftsman\CQRS\HandlerWrapper\HandlerWrapperInterface;
use DigitalCraftsman\CQRS\Query\Query;
use DigitalCraftsman\CQRS\RequestDataTransformer\RequestDataTransformerInterface;
use DigitalCraftsman\CQRS\RequestDecoder\RequestDecoderInterface;
use DigitalCraftsman\CQRS\RequestValidator\RequestValidatorInterface;
use DigitalCraftsman\CQRS\ResponseConstructor\ResponseConstructorInterface;
use DigitalCraftsman\CQRS\ServiceMap\ServiceMap;
use DigitalCraftsman\CQRS\ValueObject\RoutePayload;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class QueryController extends AbstractController
{
    /**
     * @param array<class-string<RequestValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null       $defaultRequestValidatorClasses
     * @param class-string<RequestDecoderInterface>|null                                                           $defaultRequestDecoderClass
     * @param array<class-string<RequestDataTransformerInterface>, scalar|array<array-key, scalar|null>|null>|null $defaultRequestDataTransformerClasses
     * @param class-string<DTOConstructorInterface>|null                                                           $defaultDTOConstructorClass
     * @param array<class-string<DTOValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null           $defaultDTOValidatorClasses
     * @param array<class-string<HandlerWrapperInterface>, scalar|array<array-key, scalar|null>|null>|null         $defaultHandlerWrapperClasses
     * @param class-string<ResponseConstructorInterface>|null                                                      $defaultResponseConstructorClass
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

    /** We don't type the $routePayload because we never trigger it manually, it's only supplied through Symfony. */
    public function handle(
        Request $request,
        array $routePayload,
    ): Response {
        /** @psalm-suppress MixedArgumentTypeCoercion */
        $configuration = RoutePayload::fromPayload($routePayload);

        // -- Validate request
        $requestValidatorClasses = $this->mergeClasses(
            $configuration->requestValidatorClasses,
            $this->defaultRequestValidatorClasses,
        );
        foreach ($requestValidatorClasses as $requestValidatorClass => $parameters) {
            $requestValidator = $this->serviceMap->getRequestValidator($requestValidatorClass);
            $requestValidator->validateRequest($request, $parameters);
        }

        // -- Get request data from request
        $requestDecoder = $this->serviceMap->getRequestDecoder($configuration->requestDecoderClass, $this->defaultRequestDecoderClass);
        $requestData = $requestDecoder->decodeRequest($request);

        // -- Transform request data
        $requestDataTransformerClasses = $this->mergeClasses(
            $configuration->requestDataTransformerClasses,
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

        /** @var Query $query */
        $query = $dtoConstructor->constructDTO($requestData, $configuration->dtoClass);

        // -- Validate query
        $dtoValidatorClasses = $this->mergeClasses(
            $configuration->dtoValidatorClasses,
            $this->defaultDTOValidatorClasses,
        );
        foreach ($dtoValidatorClasses as $dtoValidatorClass => $parameters) {
            $dtoValidator = $this->serviceMap->getDTOValidator($dtoValidatorClass);
            $dtoValidator->validateDTO($request, $query, $parameters);
        }

        // -- Wrap handlers
        /** The wrapper handlers are quite complex, so additional explanation can be found in @HandlerWrapperStep */
        $handlerWrapperClasses = $this->mergeClasses(
            $configuration->handlerWrapperClasses,
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
        /** @psalm-suppress PossiblyInvalidArgument */
        $queryHandler = $this->serviceMap->getQueryHandler($configuration->handlerClass);

        $result = null;

        try {
            $result = $queryHandler->handle($query);

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

    /**
     * Classes with parameters are taken from request configuration if available. Otherwise, the ones from default are used.
     *
     * @template T of RequestValidatorInterface|RequestDataTransformerInterface|DTOValidatorInterface|HandlerWrapperInterface
     *
     * @param array<class-string<T>, scalar|array<array-key, scalar|null>|null>|null $classesFromRoute
     * @param array<class-string<T>, scalar|array<array-key, scalar|null>|null>|null $classesFromDefault
     *
     * @return array<class-string<T>, scalar|array<array-key, scalar|null>|null>
     */
    public function mergeClasses(
        ?array $classesFromRoute,
        ?array $classesFromDefault,
    ): array {
        return $classesFromRoute
            ?? $classesFromDefault
            ?? [];
    }
}
