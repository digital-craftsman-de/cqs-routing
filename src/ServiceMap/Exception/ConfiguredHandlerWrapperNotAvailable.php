<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ServiceMap\Exception;

/** @psalm-immutable */
final class ConfiguredHandlerWrapperNotAvailable extends \DomainException
{
    public function __construct()
    {
        parent::__construct('The configured handler wrapper is not available');
    }
}
