<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ServiceMap;

use DigitalCraftsman\CQRS\Command\CommandHandlerInterface;
use DigitalCraftsman\CQRS\DTO\HandlerWrapperConfiguration;
use DigitalCraftsman\CQRS\DTOConstructor\DTOConstructorInterface;
use DigitalCraftsman\CQRS\DTODataTransformer\DTODataTransformerInterface;
use DigitalCraftsman\CQRS\DTOValidator\DTOValidatorInterface;
use DigitalCraftsman\CQRS\HandlerWrapper\DTO\HandlerWrapperWithParameters;
use DigitalCraftsman\CQRS\HandlerWrapper\HandlerWrapperInterface;
use DigitalCraftsman\CQRS\Query\QueryHandlerInterface;
use DigitalCraftsman\CQRS\RequestDecoder\RequestDecoderInterface;
use DigitalCraftsman\CQRS\ResponseConstructor\ResponseConstructorInterface;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredCommandHandlerNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredDTOConstructorNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredDTODataTransformerNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredDTOValidatorNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredHandlerWrapperNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredQueryHandlerNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredRequestDecoderNotAvailable;
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
        private ServiceProviderInterface $requestDecoders,
        private ServiceProviderInterface $dtoDataTransformers,
        private ServiceProviderInterface $dtoConstructors,
        private ServiceProviderInterface $dtoValidators,
        private ServiceProviderInterface $handlerWrappers,
        private ServiceProviderInterface $commandHandlers,
        private ServiceProviderInterface $queryHandlers,
        private ServiceProviderInterface $responseConstructors,
    ) {
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

    /**
     * @param array<array-key, class-string<DTODataTransformerInterface>>|null $dtoDataTransformerClasses
     * @param array<array-key, class-string<DTODataTransformerInterface>>|null $defaultDTODataTransformerClasses
     *
     * @return array<array-key, DTODataTransformerInterface>
     */
    public function getDTODataTransformers(?array $dtoDataTransformerClasses, ?array $defaultDTODataTransformerClasses): array
    {
        if ($dtoDataTransformerClasses === null && $defaultDTODataTransformerClasses === null) {
            return [];
        }

        $selectedDTODataTransformerClasses = $dtoDataTransformerClasses ?? $defaultDTODataTransformerClasses;

        return array_map(
            fn (string $dtoDataTransformerClass) => $this->dtoDataTransformers->has($dtoDataTransformerClass)
                ? $this->dtoDataTransformers->get($dtoDataTransformerClass)
                : throw new ConfiguredDTODataTransformerNotAvailable($dtoDataTransformerClass),
            $selectedDTODataTransformerClasses,
        );
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

    /**
     * @param array<array-key, class-string<DTOValidatorInterface>>|null $dtoValidatorClasses
     * @param array<array-key, class-string<DTOValidatorInterface>>|null $defaultDTOValidatorClasses
     *
     * @return array<array-key, DTOValidatorInterface>
     */
    public function getDTOValidators(?array $dtoValidatorClasses, ?array $defaultDTOValidatorClasses): array
    {
        if ($dtoValidatorClasses === null && $defaultDTOValidatorClasses === null) {
            return [];
        }

        $selectedDTOValidatorClasses = $dtoValidatorClasses ?? $defaultDTOValidatorClasses;

        return array_map(
            fn (string $dtoValidatorClass) => $this->dtoValidators->has($dtoValidatorClass)
                ? $this->dtoValidators->get($dtoValidatorClass)
                : throw new ConfiguredDTOValidatorNotAvailable($dtoValidatorClass),
            $selectedDTOValidatorClasses,
        );
    }

    /**
     * @param array<int, HandlerWrapperConfiguration>|null           $handlerWrapperConfigurations
     * @param array<int, class-string<HandlerWrapperInterface>>|null $defaultHandlerWrapperClasses
     *
     * @return array<array-key, HandlerWrapperWithParameters>
     */
    public function getHandlerWrappersWithParameters(?array $handlerWrapperConfigurations, ?array $defaultHandlerWrapperClasses): array
    {
        if ($handlerWrapperConfigurations === null && $defaultHandlerWrapperClasses === null) {
            return [];
        }

        if ($handlerWrapperConfigurations === null) {
            return array_map(
                fn (string $handlerWrapperClass) => new HandlerWrapperWithParameters(
                    $this->handlerWrappers->has($handlerWrapperClass)
                        ? $this->handlerWrappers->get($handlerWrapperClass)
                        : throw new ConfiguredHandlerWrapperNotAvailable($handlerWrapperClass),
                    null,
                ),
                $defaultHandlerWrapperClasses,
            );
        }

        return array_map(
            fn (HandlerWrapperConfiguration $handlerWrapperConfiguration) => new HandlerWrapperWithParameters(
                $this->handlerWrappers->has($handlerWrapperConfiguration->handlerWrapperClass)
                    ? $this->handlerWrappers->get($handlerWrapperConfiguration->handlerWrapperClass)
                    : throw new ConfiguredHandlerWrapperNotAvailable($handlerWrapperConfiguration->handlerWrapperClass),
                $handlerWrapperConfiguration->parameters,
            ),
            $handlerWrapperConfigurations,
        );
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
