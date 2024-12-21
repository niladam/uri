<?php

namespace Niladam\Uri\Traits;

trait Tappable
{
    public function tap(callable $callback): self
    {
        $callback($this);

        return $this;
    }
}
