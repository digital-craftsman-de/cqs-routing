<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ResponseConstructor;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface ResponseConstructorInterface
{
    /** @param ?mixed $data */
    public function constructResponse($data, Request $request): Response;
}
