<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\RequestDecoder;

use Symfony\Component\HttpFoundation\Request;

interface RequestDecoderInterface
{
    public function decodeRequest(Request $request): array;
}
