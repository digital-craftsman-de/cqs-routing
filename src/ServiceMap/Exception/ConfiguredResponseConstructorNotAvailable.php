<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ServiceMap\Exception;

/** @psalm-immutable */
final class ConfiguredResponseConstructorNotAvailable extends \DomainException
{
    public function __construct()
    {
        parent::__construct('The configured response constructor is not available');
    }
}
