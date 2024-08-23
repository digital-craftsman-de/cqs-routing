<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test\Domain\Tasks\WriteSide\CreateTask;

use DigitalCraftsman\CQSRouting\RequestDecoder\RequestDecoderInterface;
use Symfony\Component\HttpFoundation\Request;

final class CreateTaskRequestDecoder implements RequestDecoderInterface
{
    public function decodeRequest(Request $request): array
    {
        return [
            'title' => $request->get('title'),
            'content' => $request->get('content'),
            'priority' => $request->get('priority'),
        ];
    }
}
