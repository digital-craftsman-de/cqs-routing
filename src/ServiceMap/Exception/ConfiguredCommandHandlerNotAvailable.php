<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ServiceMap\Exception;

/** @psalm-immutable */
final class ConfiguredCommandHandlerNotAvailable extends \DomainException
{
    public function __construct()
    {
        parent::__construct('The configured command handler is not available');
    }
}
