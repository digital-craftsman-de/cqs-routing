<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ServiceMap\Exception;

/** @psalm-immutable */
final class ConfiguredDTOConstructorNotAvailable extends \DomainException
{
    public function __construct()
    {
        parent::__construct('The configured DTO constructor is not available');
    }
}
