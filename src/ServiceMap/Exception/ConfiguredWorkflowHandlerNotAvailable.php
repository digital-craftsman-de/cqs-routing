<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ServiceMap\Exception;

/** @psalm-immutable */
final class ConfiguredWorkflowHandlerNotAvailable extends \DomainException
{
    public function __construct(string $handlerClass)
    {
        parent::__construct(sprintf('The configured workflow handler "%s" is not available', $handlerClass));
    }
}
