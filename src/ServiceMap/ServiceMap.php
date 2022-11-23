<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ServiceMap;

use DigitalCraftsman\CQRS\Command\CommandHandlerInterface;
use DigitalCraftsman\CQRS\DTOConstructor\DTOConstructorInterface;
use DigitalCraftsman\CQRS\DTOValidator\DTOValidatorInterface;
use DigitalCraftsman\CQRS\HandlerWrapper\HandlerWrapperInterface;
use DigitalCraftsman\CQRS\Query\QueryHandlerInterface;
use DigitalCraftsman\CQRS\RequestDataTransformer\RequestDataTransformerInterface;
use DigitalCraftsman\CQRS\RequestDecoder\RequestDecoderInterface;
use DigitalCraftsman\CQRS\RequestValidator\RequestValidatorInterface;
use DigitalCraftsman\CQRS\ResponseConstructor\ResponseConstructorInterface;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredCommandHandlerNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredDTOConstructorNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredDTOValidatorNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredHandlerWrapperNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredQueryHandlerNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredRequestDataTransformerNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredRequestDecoderNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredRequestValidatorNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredResponseConstructorNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\DTOConstructorOrDefaultDTOConstructorMustBeConfigured;
use DigitalCraftsman\CQRS\ServiceMap\Exception\RequestDecoderOrDefaultRequestDecoderMustBeConfigured;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ResponseConstructorOrDefaultResponseConstructorMustBeConfigured;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Contracts\Service\ServiceProviderInterface;

/** @internal */
final class ServiceMap
{
    public function __construct(
        private readonly ServiceProviderInterface $requestValidators,
        private readonly ServiceProviderInterface $requestDecoders,
        private readonly ServiceProviderInterface $requestDataTransformers,
        private readonly ServiceProviderInterface $dtoConstructors,
        private readonly ServiceProviderInterface $dtoValidators,
        private readonly ServiceProviderInterface $handlerWrappers,
        private readonly ServiceProviderInterface $commandHandlers,
        private readonly ServiceProviderInterface $queryHandlers,
        private readonly ServiceProviderInterface $responseConstructors,
    ) {
    }

    /** @param class-string<RequestValidatorInterface> $requestValidatorClass */
    public function getRequestValidator(string $requestValidatorClass): RequestValidatorInterface
    {
        try {
            return $this->requestValidators->get($requestValidatorClass);
        } catch (ContainerExceptionInterface) {
            throw new ConfiguredRequestValidatorNotAvailable($requestValidatorClass);
        }
    }

    /**
     * @param class-string<RequestDecoderInterface>|null $requestDecoderClass
     * @param class-string<RequestDecoderInterface>|null $defaultRequestDecoderClass
     */
    public function getRequestDecoder(?string $requestDecoderClass, ?string $defaultRequestDecoderClass): RequestDecoderInterface
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

    /** @param class-string<RequestDataTransformerInterface> $requestDataTransformerClass */
    public function getRequestDataTransformer(string $requestDataTransformerClass): RequestDataTransformerInterface
    {
        try {
            return $this->requestDataTransformers->get($requestDataTransformerClass);
        } catch (ContainerExceptionInterface) {
            throw new ConfiguredRequestDataTransformerNotAvailable($requestDataTransformerClass);
        }
    }

    /**
     * @param class-string<DTOConstructorInterface>|null $dtoConstructorClass
     * @param class-string<DTOConstructorInterface>|null $defaultDTOConstructorClass
     */
    public function getDTOConstructor(?string $dtoConstructorClass, ?string $defaultDTOConstructorClass): DTOConstructorInterface
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

    /** @param class-string<DTOValidatorInterface> $dtoValidatorClass */
    public function getDTOValidator(string $dtoValidatorClass): DTOValidatorInterface
    {
        try {
            return $this->dtoValidators->get($dtoValidatorClass);
        } catch (ContainerExceptionInterface) {
            throw new ConfiguredDTOValidatorNotAvailable($dtoValidatorClass);
        }
    }

    /** @param class-string<HandlerWrapperInterface> $handlerWrapperClass */
    public function getHandlerWrapper(string $handlerWrapperClass): HandlerWrapperInterface
    {
        try {
            return $this->handlerWrappers->get($handlerWrapperClass);
        } catch (ContainerExceptionInterface) {
            throw new ConfiguredHandlerWrapperNotAvailable($handlerWrapperClass);
        }
    }

    /** @param class-string<CommandHandlerInterface> $handlerClass */
    public function getCommandHandler(string $handlerClass): CommandHandlerInterface
    {
        try {
            return $this->commandHandlers->get($handlerClass);
        } catch (ContainerExceptionInterface) {
            throw new ConfiguredCommandHandlerNotAvailable($handlerClass);
        }
    }

    /** @param class-string<QueryHandlerInterface> $handlerClass */
    public function getQueryHandler(string $handlerClass): QueryHandlerInterface
    {
        try {
            return $this->queryHandlers->get($handlerClass);
        } catch (ContainerExceptionInterface) {
            throw new ConfiguredQueryHandlerNotAvailable($handlerClass);
        }
    }

    /** @param class-string<ResponseConstructorInterface> $responseConstructorClass */
    public function getResponseConstructor(
        ?string $responseConstructorClass,
        ?string $defaultResponseConstructorClass,
    ): ResponseConstructorInterface {
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
