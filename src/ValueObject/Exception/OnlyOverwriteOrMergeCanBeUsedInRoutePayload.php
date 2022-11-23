<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ValueObject\Exception;

/**
 * @psalm-immutable
 *
 * @codeCoverageIgnore
 *
 * @internal
 */
final class OnlyOverwriteOrMergeCanBeUsedInRoutePayload extends \InvalidArgumentException
{
    public function __construct()
    {
        parent::__construct('You can only use an overwrite or a merge list of classes');
    }
}
