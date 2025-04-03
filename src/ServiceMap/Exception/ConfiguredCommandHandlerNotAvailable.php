<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\ServiceMap\Exception;

/**
 * @psalm-immutable
 *
 * @internal
 */
final class ConfiguredCommandHandlerNotAvailable extends \DomainException
{
    public function __construct(string $handlerClass)
    {
        parent::__construct(sprintf('The configured command handler "%s" is not available', $handlerClass));
    }
}
