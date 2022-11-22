<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\DTO\Exception;

/**
 * @psalm-immutable
 *
 * @codeCoverageIgnore
 */
final class InvalidConfiguration extends \InvalidArgumentException
{
    public function __construct(mixed $class)
    {
        parent::__construct(sprintf('The class %s is invalid', $class));
    }
}
