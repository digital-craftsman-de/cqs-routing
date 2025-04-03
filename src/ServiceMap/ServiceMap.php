<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\ServiceMap;

use DigitalCraftsman\CQSRouting\Command\CommandHandler;
use DigitalCraftsman\CQSRouting\DTOConstructor\DTOConstructor;
use DigitalCraftsman\CQSRouting\DTOValidator\DTOValidator;
use DigitalCraftsman\CQSRouting\HandlerWrapper\HandlerWrapper;
use DigitalCraftsman\CQSRouting\Query\QueryHandler;
use DigitalCraftsman\CQSRouting\RequestDataTransformer\RequestDataTransformer;
use DigitalCraftsman\CQSRouting\RequestDecoder\RequestDecoder;
use DigitalCraftsman\CQSRouting\RequestValidator\RequestValidator;
use DigitalCraftsman\CQSRouting\ResponseConstructor\ResponseConstructor;
use DigitalCraftsman\CQSRouting\ServiceMap\Exception\ConfiguredCommandHandlerNotAvailable;
use DigitalCraftsman\CQSRouting\ServiceMap\Exception\ConfiguredDTOConstructorNotAvailable;
use DigitalCraftsman\CQSRouting\ServiceMap\Exception\ConfiguredDTOValidatorNotAvailable;
use DigitalCraftsman\CQSRouting\ServiceMap\Exception\ConfiguredHandlerWrapperNotAvailable;
use DigitalCraftsman\CQSRouting\ServiceMap\Exception\ConfiguredQueryHandlerNotAvailable;
use DigitalCraftsman\CQSRouting\ServiceMap\Exception\ConfiguredRequestDataTransformerNotAvailable;
use DigitalCraftsman\CQSRouting\ServiceMap\Exception\ConfiguredRequestDecoderNotAvailable;
use DigitalCraftsman\CQSRouting\ServiceMap\Exception\ConfiguredRequestValidatorNotAvailable;
use DigitalCraftsman\CQSRouting\ServiceMap\Exception\ConfiguredResponseConstructorNotAvailable;
use DigitalCraftsman\CQSRouting\ServiceMap\Exception\DTOConstructorOrDefaultDTOConstructorMustBeConfigured;
use DigitalCraftsman\CQSRouting\ServiceMap\Exception\RequestDecoderOrDefaultRequestDecoderMustBeConfigured;
use DigitalCraftsman\CQSRouting\ServiceMap\Exception\ResponseConstructorOrDefaultResponseConstructorMustBeConfigured;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Contracts\Service\ServiceProviderInterface;

/**
 * @internal
 */
final readonly class ServiceMap
{
    public function __construct(
        private ServiceProviderInterface $requestValidators,
        private ServiceProviderInterface $requestDecoders,
        private ServiceProviderInterface $requestDataTransformers,
        private ServiceProviderInterface $dtoConstructors,
        private ServiceProviderInterface $dtoValidators,
        private ServiceProviderInterface $handlerWrappers,
        private ServiceProviderInterface $commandHandlers,
        private ServiceProviderInterface $queryHandlers,
        private ServiceProviderInterface $responseConstructors,
    ) {
    }

    /**
     * @param class-string<RequestValidator> $requestValidatorClass
     */
    public function getRequestValidator(string $requestValidatorClass): RequestValidator
    {
        try {
            return $this->requestValidators->get($requestValidatorClass);
        } catch (ContainerExceptionInterface) {
            throw new ConfiguredRequestValidatorNotAvailable($requestValidatorClass);
        }
    }

    /**
     * @param class-string<RequestDecoder>|null $requestDecoderClass
     * @param class-string<RequestDecoder>|null $defaultRequestDecoderClass
     */
    public function getRequestDecoder(?string $requestDecoderClass, ?string $defaultRequestDecoderClass): RequestDecoder
    {
        $selectedRequestDecoderClass = $requestDecoderClass ?? $defaultRequestDecoderClass;
        if ($selectedRequestDecoderClass === null) {
            throw new RequestDecoderOrDefaultRequestDecoderMustBeConfigured();
        }

        try {
            return $this->requestDecoders->get($selectedRequestDecoderClass);
        } catch (NotFoundExceptionInterface) {
            throw new ConfiguredRequestDecoderNotAvailable($selectedRequestDecoderClass);
        }
    }

    /**
     * @param class-string<RequestDataTransformer> $requestDataTransformerClass
     */
    public function getRequestDataTransformer(string $requestDataTransformerClass): RequestDataTransformer
    {
        try {
            return $this->requestDataTransformers->get($requestDataTransformerClass);
        } catch (ContainerExceptionInterface) {
            throw new ConfiguredRequestDataTransformerNotAvailable($requestDataTransformerClass);
        }
    }

    /**
     * @param class-string<DTOConstructor>|null $dtoConstructorClass
     * @param class-string<DTOConstructor>|null $defaultDTOConstructorClass
     */
    public function getDTOConstructor(?string $dtoConstructorClass, ?string $defaultDTOConstructorClass): DTOConstructor
    {
        $selectedDTOConstructorClass = $dtoConstructorClass ?? $defaultDTOConstructorClass;
        if ($selectedDTOConstructorClass === null) {
            throw new DTOConstructorOrDefaultDTOConstructorMustBeConfigured();
        }

        try {
            return $this->dtoConstructors->get($selectedDTOConstructorClass);
        } catch (ContainerExceptionInterface) {
            throw new ConfiguredDTOConstructorNotAvailable($selectedDTOConstructorClass);
        }
    }

    /**
     * @param class-string<DTOValidator> $dtoValidatorClass
     */
    public function getDTOValidator(string $dtoValidatorClass): DTOValidator
    {
        try {
            return $this->dtoValidators->get($dtoValidatorClass);
        } catch (ContainerExceptionInterface) {
            throw new ConfiguredDTOValidatorNotAvailable($dtoValidatorClass);
        }
    }

    /**
     * @param class-string<HandlerWrapper> $handlerWrapperClass
     */
    public function getHandlerWrapper(string $handlerWrapperClass): HandlerWrapper
    {
        try {
            return $this->handlerWrappers->get($handlerWrapperClass);
        } catch (ContainerExceptionInterface) {
            throw new ConfiguredHandlerWrapperNotAvailable($handlerWrapperClass);
        }
    }

    /**
     * @param class-string<CommandHandler> $handlerClass
     */
    public function getCommandHandler(string $handlerClass): CommandHandler
    {
        try {
            return $this->commandHandlers->get($handlerClass);
        } catch (ContainerExceptionInterface) {
            throw new ConfiguredCommandHandlerNotAvailable($handlerClass);
        }
    }

    /**
     * @param class-string<QueryHandler> $handlerClass
     */
    public function getQueryHandler(string $handlerClass): QueryHandler
    {
        try {
            return $this->queryHandlers->get($handlerClass);
        } catch (ContainerExceptionInterface) {
            throw new ConfiguredQueryHandlerNotAvailable($handlerClass);
        }
    }

    /**
     * @param class-string<ResponseConstructor> $responseConstructorClass
     */
    public function getResponseConstructor(
        ?string $responseConstructorClass,
        ?string $defaultResponseConstructorClass,
    ): ResponseConstructor {
        $selectedResponseConstructorClass = $responseConstructorClass ?? $defaultResponseConstructorClass;
        if ($selectedResponseConstructorClass === null) {
            throw new ResponseConstructorOrDefaultResponseConstructorMustBeConfigured();
        }

        try {
            return $this->responseConstructors->get($selectedResponseConstructorClass);
        } catch (ContainerExceptionInterface) {
            throw new ConfiguredResponseConstructorNotAvailable($selectedResponseConstructorClass);
        }
    }
}
