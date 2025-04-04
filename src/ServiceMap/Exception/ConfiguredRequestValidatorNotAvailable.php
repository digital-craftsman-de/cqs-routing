<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\ServiceMap\Exception;

/**
 * @psalm-immutable
 *
 * @internal
 */
final class ConfiguredRequestValidatorNotAvailable extends \DomainException
{
    public function __construct(string $requestValidatorClass)
    {
        parent::__construct(sprintf('The configured request validator "%s" is not available', $requestValidatorClass));
    }
}
