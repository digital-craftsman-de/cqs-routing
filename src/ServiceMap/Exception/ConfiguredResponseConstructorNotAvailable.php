<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\ServiceMap\Exception;

/**
 * @psalm-immutable
 *
 * @internal
 */
final class ConfiguredResponseConstructorNotAvailable extends \DomainException
{
    public function __construct(string $responseConstructorClass)
    {
        parent::__construct(sprintf('The configured response constructor "%s" is not available', $responseConstructorClass));
    }
}
