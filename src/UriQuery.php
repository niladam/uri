<?php

declare(strict_types=1);

namespace Niladam\Uri;

class UriQuery
{
    protected Uri $uri;

    public function __construct(Uri $uri)
    {
        $this->uri = $uri;
    }

    public function all(): array
    {
        $raw = $this->value();
        if ($raw === '') {
            return [];
        }

        $pairs  = explode('&', $raw);
        $result = [];

        foreach ($pairs as $pair) {
            if (strpos($pair, '=') === false) {
                $key = urldecode($pair);
                $this->appendValue($result, $key, '');
            } else {
                [$k, $v] = explode('=', $pair, 2);
                $key     = urldecode($k);
                $val     = urldecode($v);

                $this->appendValue($result, $key, $val);
            }
        }

        return $result;
    }

    protected function appendValue(array &$arr, string $key, $value): void
    {
        if (! array_key_exists($key, $arr)) {
            $arr[$key] = $value;
        } else {
            if (! is_array($arr[$key])) {
                $arr[$key] = [$arr[$key]];
            }
            $arr[$key][] = $value;
        }
    }

    public function get(?string $key = null, $default = null)
    {
        $all = $this->all();

        if ($key === null) {
            return $all;
        }

        return $all[$key] ?? $default;
    }

    public function decode(): string
    {
        return rawurldecode($this->value());
    }

    public function value(): string
    {
        return (string)$this->uri->getUri()->getQuery();
    }

    public function toArray(): array
    {
        return $this->all();
    }

    public function __toString(): string
    {
        return $this->value();
    }

    public function integer(string $key): ?int
    {
        $val = $this->get($key);

        if (is_array($val)) {
            return null;
        }

        return is_numeric($val) ? (int)$val : null;
    }
}
