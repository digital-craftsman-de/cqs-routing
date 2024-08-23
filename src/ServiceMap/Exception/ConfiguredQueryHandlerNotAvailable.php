<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\ServiceMap\Exception;

/**
 * @psalm-immutable
 *
 * @codeCoverageIgnore
 *
 * @internal
 */
final class ConfiguredQueryHandlerNotAvailable extends \DomainException
{
    public function __construct(string $handlerClass)
    {
        parent::__construct(sprintf('The configured query handler "%s" is not available', $handlerClass));
    }
}
