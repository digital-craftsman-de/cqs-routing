<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ServiceMap\Exception;

/**
 * @psalm-immutable
 * @codeCoverageIgnore
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
