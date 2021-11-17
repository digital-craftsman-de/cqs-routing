<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\HandlerWrapper\DTO;

use DigitalCraftsman\CQRS\HandlerWrapper\HandlerWrapperInterface;

final class HandlerWrapperWithParameters
{
    /** @psalm-param array<int, string|int|float|bool>|string|int|float|bool|null $parameters */
    public function __construct(
        public HandlerWrapperInterface $handlerWrapper,
        public mixed $parameters,
    ) {
    }
}
