<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test\Application\Authentication\Exception;

use DigitalCraftsman\CQSRouting\Command\Command;
use DigitalCraftsman\CQSRouting\Query\Query;

/** @psalm-immutable  */
final class NotRelevantForDTO extends \InvalidArgumentException
{
    public function __construct(Command | Query $dto)
    {
        parent::__construct(sprintf(
            'The supplied action of type %s does not have the relevant property',
            get_class($dto),
        ));
    }
}
