<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AppTestCase extends KernelTestCase
{
    /**
     * @template T
     *
     * @psalm-param class-string<T> $serviceClass
     *
     * @return T
     */
    protected function getContainerService(string $serviceClass): object
    {
        /** @psalm-var T */
        return $this::getContainer()->get($serviceClass);
    }
}
