<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\ServiceMap\Exception;

/**
 * @psalm-immutable
 *
 * @internal
 */
final class ConfiguredDTOConstructorNotAvailable extends \DomainException
{
    public function __construct(string $dtoConstructorClass)
    {
        parent::__construct(sprintf('The configured DTO constructor "%s" is not available', $dtoConstructorClass));
    }
}
