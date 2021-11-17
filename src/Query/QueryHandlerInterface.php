<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Query;

interface QueryHandlerInterface
{
    public function handle(Query $query);
}
