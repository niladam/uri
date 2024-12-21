<?php

namespace Niladam\Uri\Concerns;

/**
 * Interface Stringable
 * Defines a contract for objects that can be converted to a string.
 */
interface Stringable
{
    /**
     * Convert the object to its string representation.
     *
     * @return string
     */
    public function __toString(): string;
}
