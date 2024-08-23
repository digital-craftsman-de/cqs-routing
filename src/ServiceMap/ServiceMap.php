<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\ServiceMap;

use DigitalCraftsman\CQSRouting\Command\CommandHandlerInterface;
use DigitalCraftsman\CQSRouting\DTOConstructor\DTOConstructorInterface;
use DigitalCraftsman\CQSRouting\DTOValidator\DTOValidatorInterface;
use DigitalCraftsman\CQSRouting\HandlerWrapper\HandlerWrapperInterface;
use DigitalCraftsman\CQSRouting\Query\QueryHandlerInterface;
use DigitalCraftsman\CQSRouting\RequestDataTransformer\RequestDataTransformerInterface;
use DigitalCraftsman\CQSRouting\RequestDecoder\RequestDecoderInterface;
use DigitalCraftsman\CQSRouting\RequestValidator\RequestValidatorInterface;
use DigitalCraftsman\CQSRouting\ResponseConstructor\ResponseConstructorInterface;
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

/** @internal */
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
