<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ServiceMap\Exception;

/**
 * @psalm-immutable
 * @codeCoverageIgnore
 */
final class NoDefaultDTOConstructorDefined extends \DomainException
{
    public function __construct()
    {
        parent::__construct('No default DTO constructor was defined');
    }
}
