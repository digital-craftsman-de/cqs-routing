<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ServiceMap\Exception;

/**
 * @psalm-immutable
 *
 * @codeCoverageIgnore
 *
 * @internal
 */
final class ConfiguredRequestDataTransformerNotAvailable extends \DomainException
{
    public function __construct(string $requestDataTransformerClass)
    {
        parent::__construct(sprintf('The configured request data transformer "%s" is not available', $requestDataTransformerClass));
    }
}
