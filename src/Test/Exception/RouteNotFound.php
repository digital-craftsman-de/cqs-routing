<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test\Exception;

/**
 * @psalm-immutable
 *
 * @internal
 */
final class RouteNotFound extends \InvalidArgumentException
{
    public function __construct(string $routeName)
    {
        parent::__construct(sprintf(
            'Route "%s" not found.',
            $routeName,
        ));
    }
}
