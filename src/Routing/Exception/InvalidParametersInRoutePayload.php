<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Routing\Exception;

/**
 * @psalm-immutable
 *
 * @codeCoverageIgnore
 */
final class InvalidParametersInRoutePayload extends \InvalidArgumentException
{
    public function __construct(mixed $class)
    {
        parent::__construct(sprintf('Invalid parameters for class %s in route payload', $class));
    }
}
