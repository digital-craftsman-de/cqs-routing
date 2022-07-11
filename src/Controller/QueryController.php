<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Controller;

use DigitalCraftsman\CQRS\DTO\RouteConfiguration;
use DigitalCraftsman\CQRS\DTOConstructor\DTOConstructorInterface;
use DigitalCraftsman\CQRS\DTODataTransformer\DTODataTransformerInterface;
use DigitalCraftsman\CQRS\DTOValidator\DTOValidatorInterface;
use DigitalCraftsman\CQRS\HandlerWrapper\DTO\HandlerWrapperStep;
use DigitalCraftsman\CQRS\HandlerWrapper\HandlerWrapperInterface;
use DigitalCraftsman\CQRS\Query\Query;
use DigitalCraftsman\CQRS\RequestDecoder\RequestDecoderInterface;
use DigitalCraftsman\CQRS\ResponseConstructor\ResponseConstructorInterface;
use DigitalCraftsman\CQRS\ServiceMap\ServiceMap;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;

final class QueryController extends AbstractController
{
    /**
     * @psalm-param class-string<RequestDecoderInterface>|null $defaultRequestDecoderClass
     * @psalm-param array<int, class-string<DTODataTransformerInterface>>|null $defaultDTODataTransformerClasses
     * @psalm-param class-string<DTOConstructorInterface>|null $defaultDTOConstructorClass
     * @psalm-param array<int, class-string<DTOValidatorInterface>>|null $defaultDTOValidatorClasses
     * @psalm-param array<int, class-string<HandlerWrapperInterface>>|null $defaultHandlerWrapperClasses
     * @psalm-param class-string<ResponseConstructorInterface>|null $defaultResponseConstructorClass
     *
     * @codeCoverageIgnore
     */
    public function __construct(
        private ServiceMap $serviceMap,
        private ?string $defaultRequestDecoderClass,
        private ?array $defaultDTODataTransformerClasses,
        private ?string $defaultDTOConstructorClass,
        private ?array $defaultDTOValidatorClasses,
        private ?array $defaultHandlerWrapperClasses,
        private ?string $defaultResponseConstructorClass,
    ) {
    }

    public function handle(
        Request $request,
        Route $route,
    ): Response {
        /** @psalm-suppress MixedArgumentTypeCoercion */
        $routeConfiguration = RouteConfiguration::fromRouteOptions($route->getOptions());

        // Get data from request
        $requestDecoder = $this->serviceMap->getRequestDecoder($routeConfiguration->requestDecoderClass, $this->defaultRequestDecoderClass);
        $queryData = $requestDecoder->decodeRequest($request);

        // Transform data
        $dtoDataTransformers = $this->serviceMap->getDTODataTransformers(
            $routeConfiguration->dtoDataTransformerClasses,
            $this->defaultDTODataTransformerClasses,
        );
        foreach ($dtoDataTransformers as $dtoDataTransformer) {
            $queryData = $dtoDataTransformer->transformDTOData($routeConfiguration->dtoClass, $queryData);
        }

        // Construct query from data
        $dtoConstructor = $this->serviceMap->getDTOConstructor($routeConfiguration->dtoConstructorClass, $this->defaultDTOConstructorClass);

        /** @var Query $query */
        $query = $dtoConstructor->constructDTO($queryData, $routeConfiguration->dtoClass);

        // Validate query
        $dtoValidators = $this->serviceMap->getDTOValidators($routeConfiguration->dtoValidatorClasses, $this->defaultDTOValidatorClasses);
        foreach ($dtoValidators as $dtoValidator) {
            $dtoValidator->validateDTO($request, $query);
        }

        // Wrap handlers
        /** The wrapper handlers are quite complex, so additional explanation can be found in @HandlerWrapperStep */
        $handlerWrappersWithParameters = $this->serviceMap->getHandlerWrappersWithParameters(
            $routeConfiguration->handlerWrapperConfigurations,
            $this->defaultHandlerWrapperClasses,
        );

        $handlerWrapperPrepareStep = HandlerWrapperStep::prepare($handlerWrappersWithParameters);
        foreach ($handlerWrapperPrepareStep->orderedHandlerWrappersWithParameters as $handlerWrapperWithParameters) {
            $handlerWrapperWithParameters->handlerWrapper->prepare(
                $query,
                $request,
                $handlerWrapperWithParameters->parameters,
            );
        }

        // Trigger query through query handler
        /** @psalm-suppress PossiblyInvalidArgument */
        $queryHandler = $this->serviceMap->getQueryHandler($routeConfiguration->handlerClass);

        $result = null;

        try {
            $result = $queryHandler->handle($query);

            $handlerWrapperThenStep = HandlerWrapperStep::then($handlerWrappersWithParameters);
            foreach ($handlerWrapperThenStep->orderedHandlerWrappersWithParameters as $handlerWrapperWithParameters) {
                $handlerWrapperWithParameters->handlerWrapper->then(
                    $query,
                    $request,
                    $handlerWrapperWithParameters->parameters,
                );
            }
        } catch (\Exception $exception) {
            // Exception is handled by every handler wrapper until one does not return the exception anymore.
            $exceptionToHandle = $exception;
            $handlerWrapperCatchStep = HandlerWrapperStep::catch($handlerWrappersWithParameters);
            foreach ($handlerWrapperCatchStep->orderedHandlerWrappersWithParameters as $handlerWrapperWithParameters) {
                if ($exceptionToHandle !== null) {
                    $exceptionToHandle = $handlerWrapperWithParameters->handlerWrapper->catch(
                        $query,
                        $request,
                        $handlerWrapperWithParameters->parameters,
                        $exceptionToHandle,
                    );
                }
            }

            if ($exceptionToHandle !== null) {
                throw $exceptionToHandle;
            }
        }

        // Construct and return response
        $responseConstructor = $this->serviceMap->getResponseConstructor(
            $routeConfiguration->responseConstructorClass,
            $this->defaultResponseConstructorClass,
        );

        return $responseConstructor->constructResponse($result, $request);
    }
}
