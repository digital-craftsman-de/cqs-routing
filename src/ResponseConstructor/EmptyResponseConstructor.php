<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ResponseConstructor;

use Symfony\Component\HttpFoundation\Response;

final class EmptyResponseConstructor implements ResponseConstructorInterface
{
    public function constructResponse($data): Response
    {
        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
