<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ServiceMap\Exception;

/**
 * @psalm-immutable
 * @codeCoverageIgnore
 */
final class ConfiguredDTOConstructorNotAvailable extends \DomainException
{
    public function __construct(string $dtoConstructorClass)
    {
        parent::__construct(sprintf('The configured DTO constructor "%s" is not available', $dtoConstructorClass));
    }
}
