<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ValueObject\Exception;

/**
 * @psalm-immutable
 *
 * @codeCoverageIgnore
 *
 * @internal
 */
final class ClassIsNoDTOConstructor extends \InvalidArgumentException
{
    public function __construct(string $class)
    {
        parent::__construct(sprintf('The class %s is no DTO constructor', $class));
    }
}
