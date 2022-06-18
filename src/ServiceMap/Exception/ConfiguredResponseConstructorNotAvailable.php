<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ServiceMap\Exception;

/**
 * @psalm-immutable
 * @codeCoverageIgnore
 * @internal
 */
final class ConfiguredResponseConstructorNotAvailable extends \DomainException
{
    public function __construct(string $responseConstructorClass)
    {
        parent::__construct(sprintf('The configured response constructor "%s" is not available', $responseConstructorClass));
    }
}
