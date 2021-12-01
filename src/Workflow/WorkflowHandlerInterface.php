<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Workflow;

interface WorkflowHandlerInterface
{
    public function handle(Workflow $workflow): void;
}
