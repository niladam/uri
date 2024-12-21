<?php

declare(strict_types=1);

namespace Niladam\Uri;

use League\Uri\Uri as LeagueUri;
use Niladam\Uri\Concerns\Arrayable;
use Niladam\Uri\Concerns\Htmlable;
use Niladam\Uri\Concerns\Stringable;
use Niladam\Uri\Traits\Conditionable;
use Niladam\Uri\Traits\Dumpable;
use Niladam\Uri\Traits\Macroable;

class Uri implements Stringable, Arrayable, Htmlable
{
    use Dumpable;
    use Conditionable;
    use Macroable;

    protected $uri;

    public function __construct($uri = '')
    {
        if ($uri instanceof UriInterface) {
            $this->uri = $uri;
        } else {
            $this->uri = LeagueUri::createFromString((string) $uri);
        }
    }

    public static function of($uri = ''): self
    {
        return new static($uri);
    }

    public function scheme(): ?string
    {
        return $this->uri->getScheme() ?: null;
    }

    public function user(): ?string
    {
        $info = $this->uri->getUserInfo();

        return $info
            ? explode(':', $info, 2)[0]
            : null;
    }

    public function password(): ?string
    {
        $info = $this->uri->getUserInfo();

        return ($info && strpos($info, ':') !== false)
            ? explode(':', $info, 2)[1]
            : null;
    }

    public function host(): ?string
    {
        return $this->uri->getHost() ?: null;
    }

    public function port(): ?int
    {
        return $this->uri->getPort();
    }

    public function path(): string
    {
        $rawPath = $this->uri->getPath();
        $trimmed = trim($rawPath, '/');

        return $trimmed === '' ? '/' : $trimmed;
    }

    public function query(): UriQuery
    {
        return new UriQuery($this);
    }

    public function fragment(): ?string
    {
        return $this->uri->getFragment() ?: null;
    }

    public function withScheme(string $scheme): self
    {
        return new static($this->uri->withScheme($scheme));
    }

    public function withUser(?string $user = null, ?string $password = null): self
    {
        return new static($this->uri->withUserInfo($user, $password));
    }

    public function withHost(string $host): self
    {
        return new static($this->uri->withHost($host));
    }

    public function withPort(?int $port = null): self
    {
        return new static($this->uri->withPort($port));
    }

    public function withPath(string $path): self
    {
        $path = '/' . ltrim($path, '/');

        return new static($this->uri->withPath($path));
    }

    public function withQuery(array $query): self
    {
        $existing = $this->query()->all();

        foreach ($query as $key => $value) {
            if (! array_key_exists($key, $existing)) {
                $existing[$key] = $value;
            } else {
                if (! is_array($existing[$key])) {
                    $existing[$key] = [$existing[$key]];
                }
                $newValues      = is_array($value) ? $value : [$value];
                $existing[$key] = array_merge($existing[$key], $newValues);
            }
        }

        return $this->replaceQuery($existing);
    }

    public function replaceQuery(array $query): self
    {
        $qs = $this->buildQueryString($query);
        return new static($this->uri->withQuery($qs));
    }

    protected function buildQueryString(array $data): string
    {
        $parts = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // repeated key
                foreach ($value as $item) {
                    $parts[] = urlencode($key) . '=' . urlencode((string) $item);
                }
            } else {
                $parts[] = urlencode($key) . '=' . urlencode((string) $value);
            }
        }

        return implode('&', $parts);
    }

    public function withFragment(string $fragment): self
    {
        return new static($this->uri->withFragment($fragment));
    }

    /**
     * Remove one or more keys from existing query.
     *
     * @param  string|array  $keys
     */
    public function withoutQuery($keys): self
    {
        $existing = $this->query()->all();

        foreach ((array) $keys as $k) {
            unset($existing[$k]);
        }

        return $this->replaceQuery($existing);
    }

    public function pushOntoQuery(string $key, $value): self
    {
        $existing = $this->query()->all();

        if (! array_key_exists($key, $existing)) {
            $existing[$key] = is_array($value) ? $value : [$value];
        } else {
            if (! is_array($existing[$key])) {
                $existing[$key] = [$existing[$key]];
            }
            $existing[$key] = array_merge(
                $existing[$key],
                is_array($value) ? $value : [$value]
            );
        }

        return $this->replaceQuery($existing);
    }

    public function decode(): string
    {
        $original = (string) $this->uri;
        $raw      = $this->query()->value();
        $decoded  = $this->query()->decode();

        if ($raw !== '') {
            return str_replace('?' . $raw, '?' . $decoded, $original);
        }

        return $original;
    }

    public function getUri()
    {
        return $this->uri;
    }


    public function toHtml(): string
    {
        return (string) $this;
    }

    public function toArray(): array
    {
        return [
            'scheme'   => $this->scheme(),
            'user'     => $this->user(),
            'pass'     => $this->password(),
            'host'     => $this->host(),
            'port'     => $this->port(),
            'path'     => $this->path(),
            'query'    => $this->query()->all(),
            'fragment' => $this->fragment(),
        ];
    }

    public function __toString(): string
    {
        return (string) $this->uri;
    }
}
