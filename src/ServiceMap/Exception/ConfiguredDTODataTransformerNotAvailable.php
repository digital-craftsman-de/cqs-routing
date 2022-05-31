<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ServiceMap\Exception;

/**
 * @psalm-immutable
 * @codeCoverageIgnore
 */
final class ConfiguredDTODataTransformerNotAvailable extends \DomainException
{
    public function __construct(string $dtoDataTransformerClass)
    {
        parent::__construct(sprintf('The configured DTO data transformer "%s" is not available', $dtoDataTransformerClass));
    }
}
