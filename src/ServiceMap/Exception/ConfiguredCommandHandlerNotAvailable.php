<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ServiceMap\Exception;

/**
 * @psalm-immutable
 * @codeCoverageIgnore
 */
final class ConfiguredCommandHandlerNotAvailable extends \DomainException
{
    public function __construct(string $handlerClass)
    {
        parent::__construct(sprintf('The configured command handler "%s" is not available', $handlerClass));
    }
}
