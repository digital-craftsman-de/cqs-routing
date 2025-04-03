<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\RequestDecoder;

use Symfony\Component\HttpFoundation\Request;

final readonly class JsonRequestDecoder implements RequestDecoder
{
    #[\Override]
    public function decodeRequest(Request $request): array
    {
        /**
         * @var string $content
         */
        $content = $request->getContent();

        return (array) json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }
}
