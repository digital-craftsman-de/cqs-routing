<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Controller;

use DigitalCraftsman\CQRS\DTO\Configuration;
use DigitalCraftsman\CQRS\DTOConstructor\DTOConstructorInterface;
use DigitalCraftsman\CQRS\DTODataTransformer\DTODataTransformerInterface;
use DigitalCraftsman\CQRS\DTOValidator\DTOValidatorInterface;
use DigitalCraftsman\CQRS\HandlerWrapper\DTO\HandlerWrapperStep;
use DigitalCraftsman\CQRS\HandlerWrapper\HandlerWrapperInterface;
use DigitalCraftsman\CQRS\RequestDecoder\RequestDecoderInterface;
use DigitalCraftsman\CQRS\ResponseConstructor\ResponseConstructorInterface;
use DigitalCraftsman\CQRS\ServiceMap\ServiceMap;
use DigitalCraftsman\CQRS\Workflow\Workflow;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class WorkflowController extends AbstractController
{
    /**
     * @psalm-param class-string<RequestDecoderInterface>|null $defaultRequestDecoderClass
     * @psalm-param array<int, class-string<DTODataTransformerInterface>>|null $defaultDTODataTransformerClasses
     * @psalm-param class-string<DTOConstructorInterface>|null $defaultDTOConstructorClass
     * @psalm-param array<int, class-string<DTOValidatorInterface>>|null $defaultDTOValidatorClasses
     * @psalm-param array<int, class-string<HandlerWrapperInterface>>|null $defaultHandlerWrapperClasses
     * @psalm-param class-string<ResponseConstructorInterface>|null $defaultResponseConstructorClass
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

    /** We don't type the $routePayload because we never trigger it manually, it's only supplied through Symfony. */
    public function handle(
        Request $request,
        array $routePayload,
    ): Response {
        /** @psalm-suppress MixedArgumentTypeCoercion */
        $configuration = Configuration::fromRoutePayload($routePayload);

        // Get data from request
        $requestDecoder = $this->serviceMap->getRequestDecoder($configuration, $this->defaultRequestDecoderClass);
        $commandData = $requestDecoder->decodeRequest($request);

        // Transform data
        $dtoDataTransformers = $this->serviceMap->getDTODataTransformers($configuration, $this->defaultDTODataTransformerClasses);
        foreach ($dtoDataTransformers as $dtoDataTransformer) {
            $commandData = $dtoDataTransformer->transformDTOData($commandData);
        }

        // Construct command from data
        $dtoConstructor = $this->serviceMap->getDTOConstructor($configuration, $this->defaultDTOConstructorClass);

        /** @var Workflow $workflow */
        $workflow = $dtoConstructor->constructDTO($commandData, $configuration->dtoClass);

        // Validate command
        $dtoValidators = $this->serviceMap->getDTOValidators($configuration, $this->defaultDTOValidatorClasses);
        foreach ($dtoValidators as $dtoValidator) {
            $dtoValidator->validateDTO($request, $workflow);
        }

        // Wrap handlers
        /** The wrapper handlers are quite complex, so additional explanation can be found in @HandlerWrapperStep */
        $handlerWrappersWithParameters = $this->serviceMap->getHandlerWrappersWithParameters(
            $configuration,
            $this->defaultHandlerWrapperClasses,
        );

        $handlerWrapperPrepareStep = HandlerWrapperStep::prepare($handlerWrappersWithParameters);
        foreach ($handlerWrapperPrepareStep->orderedHandlerWrappersWithParameters as $handlerWrapperWithParameters) {
            $handlerWrapperWithParameters->handlerWrapper->prepare(
                $workflow,
                $handlerWrapperWithParameters->parameters,
            );
        }

        try {
            // Trigger command through command handler
            $commandHandler = $this->serviceMap->getWorkflowHandler($configuration);
            $commandHandler->handle($workflow);

            $handlerWrapperThenStep = HandlerWrapperStep::then($handlerWrappersWithParameters);
            foreach ($handlerWrapperThenStep->orderedHandlerWrappersWithParameters as $handlerWrapperWithParameters) {
                $handlerWrapperWithParameters->handlerWrapper->then(
                    $workflow,
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
                    $workflow,
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
                    $workflow,
                    $handlerWrapperWithParameters->parameters,
                );
            }
        }

        // Construct and return response
        $responseConstructor = $this->serviceMap->getResponseConstructor($configuration, $this->defaultResponseConstructorClass);

        return $responseConstructor->constructResponse(null, $request);
    }
}
