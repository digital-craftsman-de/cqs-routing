<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Routing;

use DigitalCraftsman\CQRS\Command\Command;
use DigitalCraftsman\CQRS\Command\CommandHandlerInterface;
use DigitalCraftsman\CQRS\DTOConstructor\DTOConstructorInterface;
use DigitalCraftsman\CQRS\DTOValidator\DTOValidatorInterface;
use DigitalCraftsman\CQRS\HandlerWrapper\HandlerWrapperInterface;
use DigitalCraftsman\CQRS\Query\Query;
use DigitalCraftsman\CQRS\Query\QueryHandlerInterface;
use DigitalCraftsman\CQRS\RequestDataTransformer\RequestDataTransformerInterface;
use DigitalCraftsman\CQRS\RequestDecoder\RequestDecoderInterface;
use DigitalCraftsman\CQRS\RequestValidator\RequestValidatorInterface;
use DigitalCraftsman\CQRS\ResponseConstructor\ResponseConstructorInterface;

final class RouteParameters
{
    /**
     * @param class-string<Command>|class-string<Query>                                                            $dtoClass
     * @param class-string<CommandHandlerInterface>|class-string<QueryHandlerInterface>                            $handlerClass
     * @param array<class-string<RequestValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null       $requestValidatorClasses
     * @param array<class-string<RequestValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null       $requestValidatorClassesToMergeWithDefault
     * @param class-string<RequestDecoderInterface>|null                                                           $requestDecoderClass
     * @param array<class-string<RequestDataTransformerInterface>, scalar|array<array-key, scalar|null>|null>|null $requestDataTransformerClasses
     * @param array<class-string<RequestDataTransformerInterface>, scalar|array<array-key, scalar|null>|null>|null $requestDataTransformerClassesToMergeWithDefault
     * @param class-string<DTOConstructorInterface>|null                                                           $dtoConstructorClass
     * @param array<class-string<DTOValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null           $dtoValidatorClasses
     * @param array<class-string<DTOValidatorInterface>, scalar|array<array-key, scalar|null>|null>|null           $dtoValidatorClassesToMergeWithDefault
     * @param array<class-string<HandlerWrapperInterface>, scalar|array<array-key, scalar|null>|null>|null         $handlerWrapperClasses
     * @param array<class-string<HandlerWrapperInterface>, scalar|array<array-key, scalar|null>|null>|null         $handlerWrapperClassesToMergeWithDefault
     * @param class-string<ResponseConstructorInterface>|null                                                      $responseConstructorClass
     */
    public function __construct(
        public readonly string $path,
        public readonly string $dtoClass,
        public readonly string $handlerClass,
        public readonly ?string $name = null,
        public readonly ?string $method = null,
        public readonly ?array $requestValidatorClasses = null,
        public readonly ?array $requestValidatorClassesToMergeWithDefault = null,
        public readonly ?string $requestDecoderClass = null,
        public readonly ?array $requestDataTransformerClasses = null,
        public readonly ?array $requestDataTransformerClassesToMergeWithDefault = null,
        public readonly ?string $dtoConstructorClass = null,
        public readonly ?array $dtoValidatorClasses = null,
        public readonly ?array $dtoValidatorClassesToMergeWithDefault = null,
        public readonly ?array $handlerWrapperClasses = null,
        public readonly ?array $handlerWrapperClassesToMergeWithDefault = null,
        public readonly ?string $responseConstructorClass = null,
    ) {
    }
}
