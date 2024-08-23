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
final class ClassIsNoRequestDataTransformer extends \InvalidArgumentException
{
    public function __construct(string $class)
    {
        parent::__construct(sprintf('The class %s is no request data transformer', $class));
    }
}
