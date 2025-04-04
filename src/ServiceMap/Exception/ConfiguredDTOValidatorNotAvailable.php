<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\ServiceMap\Exception;

/**
 * @psalm-immutable
 *
 * @internal
 */
final class ConfiguredDTOValidatorNotAvailable extends \DomainException
{
    public function __construct(string $dtoValidatorClass)
    {
        parent::__construct(sprintf('The configured DTO validator "%s" is not available', $dtoValidatorClass));
    }
}
