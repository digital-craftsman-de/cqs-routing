<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ServiceMap\Exception;

/** @psalm-immutable */
final class NoDefaultRequestDecoderDefined extends \DomainException
{
    public function __construct()
    {
        parent::__construct('No default request decoder was defined');
    }
}
