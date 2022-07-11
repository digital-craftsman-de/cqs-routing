<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Controller;

use DigitalCraftsman\CQRS\Command\Command;
use DigitalCraftsman\CQRS\DTO\RouteConfiguration;
use DigitalCraftsman\CQRS\DTOConstructor\DTOConstructorInterface;
use DigitalCraftsman\CQRS\DTODataTransformer\DTODataTransformerInterface;
use DigitalCraftsman\CQRS\DTOValidator\DTOValidatorInterface;
use DigitalCraftsman\CQRS\HandlerWrapper\DTO\HandlerWrapperStep;
use DigitalCraftsman\CQRS\HandlerWrapper\HandlerWrapperInterface;
use DigitalCraftsman\CQRS\RequestDecoder\RequestDecoderInterface;
use DigitalCraftsman\CQRS\ResponseConstructor\ResponseConstructorInterface;
use DigitalCraftsman\CQRS\ServiceMap\ServiceMap;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;

final class CommandController extends AbstractController
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
        $commandData = $requestDecoder->decodeRequest($request);

        // Transform data
        $dtoDataTransformers = $this->serviceMap->getDTODataTransformers(
            $routeConfiguration->dtoDataTransformerClasses,
            $this->defaultDTODataTransformerClasses,
        );
        foreach ($dtoDataTransformers as $dtoDataTransformer) {
            $commandData = $dtoDataTransformer->transformDTOData($routeConfiguration->dtoClass, $commandData);
        }

        // Construct command from data
        $dtoConstructor = $this->serviceMap->getDTOConstructor($routeConfiguration->dtoConstructorClass, $this->defaultDTOConstructorClass);

        /** @var Command $command */
        $command = $dtoConstructor->constructDTO($commandData, $routeConfiguration->dtoClass);

        // Validate command
        $dtoValidators = $this->serviceMap->getDTOValidators($routeConfiguration->dtoValidatorClasses, $this->defaultDTOValidatorClasses);
        foreach ($dtoValidators as $dtoValidator) {
            $dtoValidator->validateDTO($request, $command);
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
                $command,
                $request,
                $handlerWrapperWithParameters->parameters,
            );
        }

        try {
            // Trigger command through command handler
            /** @psalm-suppress PossiblyInvalidArgument */
            $commandHandler = $this->serviceMap->getCommandHandler($routeConfiguration->handlerClass);
            $commandHandler->handle($command);

            $handlerWrapperThenStep = HandlerWrapperStep::then($handlerWrappersWithParameters);
            foreach ($handlerWrapperThenStep->orderedHandlerWrappersWithParameters as $handlerWrapperWithParameters) {
                $handlerWrapperWithParameters->handlerWrapper->then(
                    $command,
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
                        $command,
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

        return $responseConstructor->constructResponse(null, $request);
    }
}
