<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ServiceMap\Exception;

/**
 * @psalm-immutable
 *
 * @codeCoverageIgnore
 *
 * @internal
 */
final class ResponseConstructorOrDefaultResponseConstructorMustBeConfigured extends \DomainException
{
    public function __construct()
    {
        parent::__construct(
            'There has to be ether a specific response constructor configured for the route or a default response constructor defined',
        );
    }
}
