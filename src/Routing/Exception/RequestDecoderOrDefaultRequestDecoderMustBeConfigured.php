<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Routing\Exception;

/**
 * @psalm-immutable
 *
 * @codeCoverageIgnore
 *
 * @internal
 */
final class RequestDecoderOrDefaultRequestDecoderMustBeConfigured extends \DomainException
{
    public function __construct()
    {
        parent::__construct(
            'There has to be ether a specific request decoder configured for the route or a default request decoder defined',
        );
    }
}
