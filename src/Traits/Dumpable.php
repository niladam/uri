<?php

declare(strict_types=1);

namespace Niladam\Uri\Traits;

trait Dumpable
{
    public function dump()
    {
        dump($this);

        return $this;
    }

    public function dd(): void
    {
        $this->dump();

        exit(1);
    }
}
