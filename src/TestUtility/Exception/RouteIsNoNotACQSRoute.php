<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\TestUtility\Exception;

/**
 * @psalm-immutable
 *
 * @internal
 */
final class RouteIsNoNotACQSRoute extends \InvalidArgumentException
{
    public function __construct(string $routeName)
    {
        parent::__construct(sprintf(
            'The route "%s" is not a CQS route.',
            $routeName,
        ));
    }
}
