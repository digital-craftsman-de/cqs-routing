<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\DTO;

use DigitalCraftsman\CQRS\HandlerWrapper\HandlerWrapperInterface;

/** @codeCoverageIgnore */
final class HandlerWrapperConfiguration
{
    /**
     * @psalm-param class-string<HandlerWrapperInterface> $handlerWrapperClass
     * @psalm-param array<int, string|int|float|bool>|string|int|float|bool|null $parameters
     */
    public function __construct(
        public string $handlerWrapperClass,
        public mixed $parameters = null,
    ) {
    }

    /**
     * @psalm-return array{
     *   handlerWrapperClass: class-string<HandlerWrapperInterface>,
     *   parameters: array<int, string|int|float|bool>|string|int|float|bool|null,
     * }
     */
    public function toRouteOptions(): array
    {
        return [
            'handlerWrapperClass' => $this->handlerWrapperClass,
            'parameters' => $this->parameters,
        ];
    }

    /**
     * @psalm-param array{
     *   handlerWrapperClass: class-string<HandlerWrapperInterface>,
     *   parameters: array<int, string|int|float|bool>|string|int|float|bool|null,
     * } $routingConfiguration
     */
    public static function fromRouteOptions(array $routingConfiguration): self
    {
        return new self(
            $routingConfiguration['handlerWrapperClass'],
            $routingConfiguration['parameters'],
        );
    }
}
