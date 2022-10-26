<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\HandlerWrapper\DTO;

use DigitalCraftsman\CQRS\HandlerWrapper\HandlerWrapperInterface;

/**
 * @codeCoverageIgnore
 *
 * @internal
 */
final class HandlerWrapperWithParameters
{
    /** @psalm-param array<int, string|int|float|bool>|string|int|float|bool|null $parameters */
    public function __construct(
        public readonly HandlerWrapperInterface $handlerWrapper,
        public readonly mixed $parameters,
    ) {
    }
}
