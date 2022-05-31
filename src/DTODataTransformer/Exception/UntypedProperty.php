<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\DTODataTransformer\Exception;

/**
 * @codeCoverageIgnore
 * @psalm-immutable
 */
final class UntypedProperty extends \InvalidArgumentException
{
    public function __construct()
    {
        parent::__construct('DTO data transformer can only be used with typed properties.');
    }
}
