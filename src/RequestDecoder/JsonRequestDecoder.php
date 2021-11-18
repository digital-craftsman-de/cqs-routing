<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\RequestDecoder;

use Symfony\Component\HttpFoundation\Request;

final class JsonRequestDecoder implements RequestDecoderInterface
{
    public function decodeRequest(Request $request): array
    {
        /** @var string $content */
        $content = $request->getContent();

        return (array) json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }
}
