# Remap

Remap is a lightweight PHP library for mapping arrays and objects into target objects using PHP attributes.

The mapping rules live in the target class, so the output model decides how data should be read and transformed.

## Installation

```bash
composer require luimedi/remap
```

## Quick Start

```php
<?php

use Luimedi\Remap\Mapper;
use Luimedi\Remap\Attribute\ConstructorMapper;
use Luimedi\Remap\Attribute\MapProperty;
use Luimedi\Remap\Attribute\Cast\CastDateTime;

class BookEntity
{
	public function __construct(
		public string $title,
		public string $author,
		public DateTimeInterface $publishedAt,
	) {}
}

#[ConstructorMapper]
class BookResource
{
	public function __construct(
		#[MapProperty(source: 'title')]
		public string $title,

		#[MapProperty(source: 'author')]
		public string $author,

		#[MapProperty(source: 'publishedAt')]
		#[CastDateTime]
		public string $publishedAt,
	) {}
}

$mapper = new Mapper();
$mapper->bind(BookEntity::class, BookResource::class);

$resource = $mapper->map(new BookEntity(
	title: 'El Hobbit',
	author: 'J.R.R. Tolkien',
	publishedAt: new DateTimeImmutable('1937-09-21'),
));
```

## How It Works

1. Define a target class.
2. Add mapping attributes to its constructor parameters or public properties.
3. Bind the source type to the target type.
4. Call `map()`.

## Mapping Styles

### Constructor Mapping

Use `#[ConstructorMapper]` when your target is created through its constructor.

```php
#[ConstructorMapper]
class UserResource
{
	public function __construct(
		#[MapProperty(source: 'username')]
		public string $username,

		#[MapProperty(source: 'registeredAt')]
		#[CastDateTime]
		public string $registeredAt,
	) {}
}
```

### Property Mapping

Use `#[PropertyMapper]` when you prefer assigning public properties.

```php
use Luimedi\Remap\Attribute\PropertyMapper;

#[PropertyMapper]
class ArticleResource
{
	#[MapProperty(source: 'title')]
	public string $title;

	#[MapProperty(source: 'body')]
	public string $body;
}
```

You can also combine both on the same class.

## Mapping Attributes

### `MapProperty`

Reads a value from an array or object property. It supports dot notation.

```php
#[MapProperty(source: 'author.name')]
public string $authorName;
```

### `MapGetter`

Calls a method on the source object.

```php
use Luimedi\Remap\Attribute\MapGetter;

#[MapGetter(source: 'getType')]
public string $type;
```

## Built-in Casts

### `CastDateTime`

Converts a `DateTimeInterface` or date string into an ISO-8601 string.

### `CastDefault`

Provides a fallback value when the mapped value is missing or empty.

```php
use Luimedi\Remap\Attribute\Cast\CastDefault;

#[MapProperty(source: 'nickname')]
#[CastDefault(default: 'anonymous')]
public string $nickname;
```

### `CastTransformer`

Maps nested arrays or objects into another target object.

```php
use Luimedi\Remap\Attribute\Cast\CastTransformer;

#[MapProperty(source: 'profile')]
#[CastTransformer]
public ?ProfileResource $profile = null;
```

### `CastIterable`

Applies another caster to each item in an iterable.

```php
use Luimedi\Remap\Attribute\Cast\CastIterable;
use Luimedi\Remap\Attribute\Cast\CastTransformer;

#[MapProperty(source: 'items')]
#[CastIterable(class: CastTransformer::class)]
public array $items = [];
```

## Dynamic Binding

Instead of binding a source type to a fixed class, you can bind it to a resolver.

```php
<?php

use Luimedi\Remap\Mapper;

$mapper = new Mapper();

$mapper->bind(UserInput::class, function ($from, $context) {
	return $from->isAdmin() ? AdminResource::class : UserResource::class;
});
```

You can also pass extra data to a mapping call:

```php
$result = $mapper->map($source, ['force_admin' => true]);
```

## Error Handling

All library-specific exceptions extend `Luimedi\Remap\Exception\RemapException`.

```php
use Luimedi\Remap\Exception\RemapException;

try {
	$result = $mapper->map($source);
} catch (RemapException $exception) {
	// Handle mapping errors here.
}
```

Common exceptions:

- `BindingNotFoundException`
- `BindingResolutionException`
- `InvalidTargetTypeException`
- `MapGetterResolutionException`
- `MissingMappedValueException`

## Custom Casts

Create custom casts by implementing `CastInterface`.

```php
<?php

namespace App\Cast;

use Attribute;
use Luimedi\Remap\Contracts\CastInterface;
use Luimedi\Remap\Contracts\ContextInterface;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class AppendSuffix implements CastInterface
{
	public function __construct(
		private string $suffix,
	) {}

	public function cast(mixed $value, ContextInterface $context): mixed
	{
		if ($value === null) {
			return null;
		}

		return (string) $value . $this->suffix;
	}
}
```

## Development

This project uses [Laravel Pint](https://laravel.com/docs/10.x/pint) to maintain a consistent code style (using the `symfony` preset).

- **Format code**: Run `composer pint` to automatically fix code style issues.
- **Check style**: Run `composer pint:test` to check for style issues without modifying files.

## License

MIT. See `LICENSE`.