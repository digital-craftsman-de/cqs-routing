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
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Contracts\Service\ServiceProviderInterface;

/**
 * @internal
 */
final readonly class ServiceMap
{
    public function __construct(
        #[AutowireIterator('cqs_routing.request_validator')]
        private ServiceProviderInterface $requestValidators,
        #[AutowireIterator('cqs_routing.request_decoder')]
        private ServiceProviderInterface $requestDecoders,
        #[AutowireIterator('cqs_routing.request_data_transformer')]
        private ServiceProviderInterface $requestDataTransformers,
        #[AutowireIterator('cqs_routing.dto_constructor')]
        private ServiceProviderInterface $dtoConstructors,
        #[AutowireIterator('cqs_routing.dto_validator')]
        private ServiceProviderInterface $dtoValidators,
        #[AutowireIterator('cqs_routing.handler_wrapper')]
        private ServiceProviderInterface $handlerWrappers,
        #[AutowireIterator('cqs_routing.command_handler')]
        private ServiceProviderInterface $commandHandlers,
        #[AutowireIterator('cqs_routing.query_handler')]
        private ServiceProviderInterface $queryHandlers,
        #[AutowireIterator('cqs_routing.response_constructor')]
        private ServiceProviderInterface $responseConstructors,
    ) {
    }

    /**
     * @template T of RequestValidator
     *
     * @param class-string<T> $requestValidatorClass
     *
     * @return T
     */
    public function getRequestValidator(string $requestValidatorClass): RequestValidator
    {
        try {
            return $this->requestValidators->get($requestValidatorClass);
        } catch (ContainerExceptionInterface) {
            throw new Exception\ConfiguredRequestValidatorNotAvailable($requestValidatorClass);
        }
    }

    /**
     * @template T of RequestDecoder
     *
     * @param class-string<T> $requestDecoderClass
     *
     * @return T
     */
    public function getRequestDecoder(string $requestDecoderClass): RequestDecoder
    {
        try {
            return $this->requestDecoders->get($requestDecoderClass);
        } catch (NotFoundExceptionInterface) {
            throw new Exception\ConfiguredRequestDecoderNotAvailable($requestDecoderClass);
        }
    }

    /**
     * @template T of RequestDataTransformer
     *
     * @param class-string<T> $requestDataTransformerClass
     *
     * @return T
     */
    public function getRequestDataTransformer(string $requestDataTransformerClass): RequestDataTransformer
    {
        try {
            return $this->requestDataTransformers->get($requestDataTransformerClass);
        } catch (ContainerExceptionInterface) {
            throw new Exception\ConfiguredRequestDataTransformerNotAvailable($requestDataTransformerClass);
        }
    }

    /**
     * @template T of DTOConstructor
     *
     * @param class-string<T> $dtoConstructorClass
     *
     * @return T
     */
    public function getDTOConstructor(string $dtoConstructorClass): DTOConstructor
    {
        try {
            return $this->dtoConstructors->get($dtoConstructorClass);
        } catch (ContainerExceptionInterface) {
            throw new Exception\ConfiguredDTOConstructorNotAvailable($dtoConstructorClass);
        }
    }

    /**
     * @template T of DTOValidator
     *
     * @param class-string<T> $dtoValidatorClass
     *
     * @return T
     */
    public function getDTOValidator(string $dtoValidatorClass): DTOValidator
    {
        try {
            return $this->dtoValidators->get($dtoValidatorClass);
        } catch (ContainerExceptionInterface) {
            throw new Exception\ConfiguredDTOValidatorNotAvailable($dtoValidatorClass);
        }
    }

    /**
     * @template T of HandlerWrapper
     *
     * @param class-string<T> $handlerWrapperClass
     *
     * @return T
     */
    public function getHandlerWrapper(string $handlerWrapperClass): HandlerWrapper
    {
        try {
            return $this->handlerWrappers->get($handlerWrapperClass);
        } catch (ContainerExceptionInterface) {
            throw new Exception\ConfiguredHandlerWrapperNotAvailable($handlerWrapperClass);
        }
    }

    /**
     * @template T of CommandHandler
     *
     * @param class-string<T> $handlerClass
     *
     * @return T
     */
    public function getCommandHandler(string $handlerClass): CommandHandler
    {
        try {
            return $this->commandHandlers->get($handlerClass);
        } catch (ContainerExceptionInterface) {
            throw new Exception\ConfiguredCommandHandlerNotAvailable($handlerClass);
        }
    }

    /**
     * @template T of QueryHandler
     *
     * @param class-string<T> $handlerClass
     *
     * @return T
     */
    public function getQueryHandler(string $handlerClass): QueryHandler
    {
        try {
            return $this->queryHandlers->get($handlerClass);
        } catch (ContainerExceptionInterface) {
            throw new Exception\ConfiguredQueryHandlerNotAvailable($handlerClass);
        }
    }

    /**
     * @template T of ResponseConstructor
     *
     * @param class-string<T> $responseConstructorClass
     *
     * @return T
     */
    public function getResponseConstructor(string $responseConstructorClass): ResponseConstructor
    {
        try {
            return $this->responseConstructors->get($responseConstructorClass);
        } catch (ContainerExceptionInterface) {
            throw new Exception\ConfiguredResponseConstructorNotAvailable($responseConstructorClass);
        }
    }
}
