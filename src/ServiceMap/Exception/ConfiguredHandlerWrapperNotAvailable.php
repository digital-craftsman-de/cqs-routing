<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ServiceMap\Exception;

/**
 * @psalm-immutable
 * @codeCoverageIgnore
 */
final class ConfiguredHandlerWrapperNotAvailable extends \DomainException
{
    public function __construct(string $handlerWrapperClass)
    {
        parent::__construct(sprintf('The configured handler wrapper "%s" is not available', $handlerWrapperClass));
    }
}
