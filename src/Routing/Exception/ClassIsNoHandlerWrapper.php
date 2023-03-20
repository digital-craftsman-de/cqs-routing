<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Routing\Exception;

/**
 * @psalm-immutable
 *
 * @codeCoverageIgnore
 *
 * @internal
 */
final class ClassIsNoHandlerWrapper extends \InvalidArgumentException
{
    public function __construct(string $class)
    {
        parent::__construct(sprintf('The class %s is no handler wrapper', $class));
    }
}
