<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ServiceMap\Exception;

/** @psalm-immutable */
final class ConfiguredQueryHandlerNotAvailable extends \DomainException
{
    public function __construct()
    {
        parent::__construct('The configured query handler is not available');
    }
}
