<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ValueObject\Exception;

/**
 * @psalm-immutable
 *
 * @codeCoverageIgnore
 */
final class OnlyOverwriteOrMergeCanBeSuppliedToRoutePayload extends \InvalidArgumentException
{
    public function __construct()
    {
        parent::__construct('You can only supply an overwrite or a merge list of classes');
    }
}
