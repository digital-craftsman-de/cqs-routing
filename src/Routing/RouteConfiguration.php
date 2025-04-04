<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Routing;

use DigitalCraftsman\CQSRouting\Command\Command;
use DigitalCraftsman\CQSRouting\Command\CommandHandler;
use DigitalCraftsman\CQSRouting\DTOConstructor\DTOConstructor;
use DigitalCraftsman\CQSRouting\DTOValidator\DTOValidator;
use DigitalCraftsman\CQSRouting\HandlerWrapper\HandlerWrapper;
use DigitalCraftsman\CQSRouting\Query\Query;
use DigitalCraftsman\CQSRouting\Query\QueryHandler;
use DigitalCraftsman\CQSRouting\RequestDataTransformer\RequestDataTransformer;
use DigitalCraftsman\CQSRouting\RequestDecoder\RequestDecoder;
use DigitalCraftsman\CQSRouting\RequestValidator\RequestValidator;
use DigitalCraftsman\CQSRouting\ResponseConstructor\ResponseConstructor;

/**
 * The configuration is the result of the route payload in combination with the defaults to be used by the controller.
 *
 * @psalm-import-type NormalizedConfigurationParameters from RoutePayload
 */
final readonly class RouteConfiguration
{
    /**
     * @param class-string<Command>|class-string<Query>                                      $dtoClass
     * @param class-string<CommandHandler>|class-string<QueryHandler>                        $handlerClass
     * @param array<class-string<RequestValidator>, NormalizedConfigurationParameters>       $requestValidatorClasses
     * @param class-string<RequestDecoder>                                                   $requestDecoderClass
     * @param array<class-string<RequestDataTransformer>, NormalizedConfigurationParameters> $requestDataTransformerClasses
     * @param class-string<DTOConstructor>                                                   $dtoConstructorClass
     * @param array<class-string<DTOValidator>, NormalizedConfigurationParameters>           $dtoValidatorClasses
     * @param array<class-string<HandlerWrapper>, NormalizedConfigurationParameters>         $handlerWrapperClasses
     * @param class-string<ResponseConstructor>                                              $responseConstructorClass
     */
    public function __construct(
        public string $dtoClass,
        public string $handlerClass,
        public array $requestValidatorClasses,
        public string $requestDecoderClass,
        public array $requestDataTransformerClasses,
        public string $dtoConstructorClass,
        public array $dtoValidatorClasses,
        public array $handlerWrapperClasses,
        public string $responseConstructorClass,
    ) {
    }
}
