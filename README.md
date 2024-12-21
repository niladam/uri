# URI

This PHP package provides a robust and flexible way to manipulate and handle URIs, including schemes, hosts, paths, queries, and fragments. It is built on top of `league/uri` and extends its functionality with an intuitive API and additional features like query string handling and fluent URI modification.

## Inspiration

This package was inspired and is based on Laravel's Uri (`Illuminate\Support\Uri`) from Laravel 11. However, since Laravel 11 requires PHP 8, this package was created to provide similar functionality for PHP 7.4. Some pieces of code have been directly copied from Laravel's implementation to ensure compatibility and feature parity.

## Features

- Parse and manipulate URIs
- Handle query strings with ease, including merging, replacing, and removing keys
- Build URIs fluently
- Decode URI strings

## Installation

Install via Composer:

```bash
composer require niladam/uri
```

## Usage

### Creating and Parsing a URI

```php
use Niladam\Uri\Uri;

$uri = Uri::of('https://user:password@example.com:8080/some/subfolder?view=full#section');

// Access URI components
$scheme   = $uri->scheme();     // "https"
$user     = $uri->user();       // "user"
$password = $uri->password();   // "password"
$host     = $uri->host();       // "example.com"
$port     = $uri->port();       // 8080
$path     = $uri->path();       // "some/subfolder"
$query    = $uri->query()->all(); // ['view' => 'full']
$fragment = $uri->fragment();   // "section"

// String representation of the URI
echo (string)$uri; // "https://user:password@example.com:8080/some/subfolder?view=full#section"
```

### Building a URI Fluently

```php
$uri = Uri::of()
    ->withScheme('https')
    ->withHost('example.com')
    ->withUser('john', 'password')
    ->withPort(1234)
    ->withPath('/account/profile')
    ->withFragment('overview')
    ->replaceQuery(['view' => 'detailed']);

echo (string)$uri; // "https://john:password@example.com:1234/account/profile?view=detailed#overview"
```

### Working with Query Strings

#### Parsing Queries

```php
$uri = Uri::of('https://example.com?name=Taylor&tags=php&tags=laravel&flagged');

// Access all query parameters
$query = $uri->query()->all();
// [
//     'name'    => 'Taylor',
//     'tags'    => ['php', 'laravel'],
//     'flagged' => ''
// ]
```

#### Modifying Queries

```php
// Merging Queries
$uri = $uri->withQuery(['tags' => 'framework', 'new' => 'value']);
// Query: "tags=php&tags=laravel&tags=framework&new=value"

// Replacing Queries
$uri = $uri->replaceQuery(['key' => 'value']);
// Query: "key=value"

// Removing Query Keys
$uri = $uri->withoutQuery(['tags', 'new']);
// Query: "key=value"

// Pushing Values onto a Query Key
$uri = $uri->pushOntoQuery('tags', 'newTag');
// Query: "tags=php&tags=laravel&tags=newTag"
```

### Decoding a URI

```php
$decodedUri = $uri->decode();
// Converts encoded URI components to their decoded equivalents
```

## Testing

Run the tests using PHPUnit:

```bash
vendor/bin/phpunit
```

## Contributing

1. Fork the repository.
2. Create a feature branch.
3. Make your changes.
4. Submit a pull request.

## License

This package is open-source and licensed under the MIT License.

