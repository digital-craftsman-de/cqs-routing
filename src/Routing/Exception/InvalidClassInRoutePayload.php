<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Routing\Exception;

/**
 * @psalm-immutable
 *
 * @codeCoverageIgnore
 */
final class InvalidClassInRoutePayload extends \InvalidArgumentException
{
    public function __construct(
        mixed $class,
        array $properties,
    ) {
        $propertyNotice = implode(' or ', $properties);

        parent::__construct(sprintf(
            'Invalid class %s in route payload for property %s',
            $class,
            $propertyNotice,
        ));
    }
}
