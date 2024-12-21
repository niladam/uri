<?php

namespace Niladam\Uri\Traits;

/**
 * Trait Conditionable
 * Provides methods for conditionally executing callbacks.
 */
trait Conditionable
{
    public function when(bool $condition, callable $callback): self
    {
        if ($condition) {
            $callback($this);
        }

        return $this;
    }

    public function unless(bool $condition, callable $callback): self
    {
        if (!$condition) {
            $callback($this);
        }

        return $this;
    }
}
