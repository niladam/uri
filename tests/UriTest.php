<?php

declare(strict_types=1);

namespace Niladam\Uri\Tests;

use Niladam\Uri\Uri;
use PHPUnit\Framework\TestCase;

class UriTest extends TestCase
{
    public function testBasicUri(): void
    {
        // Simple scenario: no query, just scheme, user, pass, host, path, fragment
        $uri = Uri::of('https://user:password@example:8080/sub/subfolder#top');

        $this->assertEquals('https', $uri->scheme());
        $this->assertEquals('user', $uri->user());
        $this->assertEquals('password', $uri->password());
        $this->assertEquals('example', $uri->host());
        $this->assertEquals(8080, $uri->port());
        $this->assertEquals('sub/subfolder', $uri->path());
        $this->assertEmpty($uri->query()->all());
        $this->assertEquals('top', $uri->fragment());

        $this->assertEquals('https://user:password@example:8080/sub/subfolder#top', (string) $uri);
    }

    public function testUriBuildingFluently(): void
    {
        $uri = Uri::of()
            ->withHost('example.com')
            ->withScheme('https')
            ->withUser('john', 'password')
            ->withPort(1234)
            ->withPath('/account/profile')
            ->withFragment('section')
            ->replaceQuery(['view' => 'full']);

        $this->assertEquals('https://john:password@example.com:1234/account/profile?view=full#section', (string) $uri);
    }

    public function testQueryStringParsing(): void
    {
        // Single-value keys, repeated keys, a "flag" key with no "="
        $uri = Uri::of('https://example.com?name=Taylor&tags=php&tags=laravel&flagged');

        // ->all() merges repeated keys into an array:
        $this->assertEquals([
            'name'    => 'Taylor',
            'tags'    => ['php', 'laravel'],
            'flagged' => '',
        ], $uri->query()->all());

        // ->decode() rawurldecodes the entire string
        $this->assertEquals('name=Taylor&tags=php&tags=laravel&flagged', $uri->query()->decode());
    }

    public function testMergingQueries(): void
    {
        // Start with a query having 'foo.bar=baz' and 'mode=dark'
        $uri = Uri::of('https://test.dev?foo.bar=baz&mode=dark');

        // Merge new keys withQuery(['foo.bar'=>'zab','extra'=>'val'])
        $uri = $uri->withQuery(['foo.bar' => 'zab', 'extra' => 'val']);

        // Because existing 'foo.bar' was 'baz', merging means both remain, repeated key:
        $this->assertEquals('foo.bar=baz&foo.bar=zab&mode=dark&extra=val', $uri->query()->decode());
    }

    public function testReplacingQueries(): void
    {
        // If we *replace* the query with new data, we discard everything else.
        $uri = Uri::of('https://test.dev?foo.bar=baz&mode=dark');
        $uri = $uri->replaceQuery(['foo.bar' => 'only']);

        $this->assertEquals('foo.bar=only', $uri->query()->decode());
    }

    public function testRemovingQueries(): void
    {
        // 'remove' some keys with ->withoutQuery(...)
        $uri = Uri::of('https://test.dev?first=1&second=2&third=3');
        $uri = $uri->withoutQuery(['first', 'third']);

        $this->assertEquals(['second' => '2'], $uri->query()->all());
    }

    public function testPushingOntoQuery(): void
    {
        // If we "pushOntoQuery" a value onto an existing key:
        $uri = Uri::of('https://example.com?tags=php');
        $uri = $uri->pushOntoQuery('tags', 'laravel');

        // 'tags' becomes an array with both 'php' and 'laravel'
        $this->assertEquals(['tags' => ['php', 'laravel']], $uri->query()->all());

        // If we push an array, it merges them in
        $uri = $uri->pushOntoQuery('tags', ['8.x','framework']);
        $this->assertEquals(['tags' => ['php','laravel','8.x','framework']], $uri->query()->all());
    }

    public function testDecodeEntireUri(): void
    {
        $uri = Uri::of('https://example/some/sub/folder')
            ->withQuery(['tags' => ['first','second']]);

        // By design, we convert arrays -> repeated keys => "?tags=first&tags=second"
        $expected = 'https://example/some/sub/folder?tags=first&tags=second';
        $this->assertEquals($expected, $uri->decode());
    }

    /**
     * @dataProvider uriDataProvider
     */
    public function testUriParsing(string $input, array $expected): void
    {
        $uri = Uri::of($input);

        $this->assertSame($expected['scheme'], $uri->scheme(), 'Scheme does not match');
        $this->assertSame($expected['user'], $uri->user(), 'User does not match');
        $this->assertSame($expected['password'], $uri->password(), 'Password does not match');
        $this->assertSame($expected['host'], $uri->host(), 'Host does not match');
        $this->assertSame($expected['port'], $uri->port(), 'Port does not match');
        $this->assertSame($expected['path'], $uri->path(), 'Path does not match');
        $this->assertSame($expected['fragment'], $uri->fragment(), 'Fragment does not match');

        if (isset($expected['query'])) {
            $this->assertSame($expected['query'], $uri->query()->all(), 'Query does not match');
        }
    }

    public function uriDataProvider(): array
    {
        return [
            'simple http url' => [
                'input'    => 'http://example.com',
                'expected' => [
                    'scheme'   => 'http',
                    'user'     => null,
                    'password' => null,
                    'host'     => 'example.com',
                    'port'     => null,
                    'path'     => '/',
                    'fragment' => null,
                ],
            ],
            'https with port and path' => [
                'input'    => 'https://example.com:8080/path',
                'expected' => [
                    'scheme'   => 'https',
                    'user'     => null,
                    'password' => null,
                    'host'     => 'example.com',
                    'port'     => 8080,
                    'path'     => 'path',
                    'fragment' => null,
                ],
            ],
            'http with query and fragment' => [
                'input'    => 'http://example.com/path?key=value#fragment',
                'expected' => [
                    'scheme'   => 'http',
                    'user'     => null,
                    'password' => null,
                    'host'     => 'example.com',
                    'port'     => null,
                    'path'     => 'path',
                    'query'    => ['key' => 'value'],
                    'fragment' => 'fragment',
                ],
            ],
            'ftp with user info' => [
                'input'    => 'ftp://user:password@ftp.example.com',
                'expected' => [
                    'scheme'   => 'ftp',
                    'user'     => 'user',
                    'password' => 'password',
                    'host'     => 'ftp.example.com',
                    'port'     => null,
                    'path'     => '/',
                    'fragment' => null,
                ],
            ],
            'ssh with path and port' => [
                'input'    => 'ssh://user@ssh.example.com:22/path',
                'expected' => [
                    'scheme'   => 'ssh',
                    'user'     => 'user',
                    'password' => null,
                    'host'     => 'ssh.example.com',
                    'port'     => 22,
                    'path'     => 'path',
                    'fragment' => null,
                ],
            ],
            'complex query with array' => [
                'input'    => 'http://example.com/path?key=value&key=another#fragment',
                'expected' => [
                    'scheme'   => 'http',
                    'user'     => null,
                    'password' => null,
                    'host'     => 'example.com',
                    'port'     => null,
                    'path'     => 'path',
                    'query'    => ['key' => ['value', 'another']],
                    'fragment' => 'fragment',
                ],
            ],
        ];
    }
}
