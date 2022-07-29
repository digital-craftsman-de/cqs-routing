<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ServiceMap\Exception;

/**
 * @psalm-immutable
 * @codeCoverageIgnore
 *
 * @internal
 */
final class ConfiguredRequestDecoderNotAvailable extends \DomainException
{
    public function __construct(string $requestDecoderClass)
    {
        parent::__construct(sprintf('The configured request decoder "%s" is not available', $requestDecoderClass));
    }
}
