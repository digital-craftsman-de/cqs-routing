<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\ResponseConstructor;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class StreamedResponseConstructor implements ResponseConstructor
{
    /**
     * @param callable $data
     */
    public function constructResponse($data, Request $request): Response
    {
        return new StreamedResponse($data);
    }
}
