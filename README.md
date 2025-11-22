# Remap

Remap is a lightweight PHP library for mapping objects to other objects using attribute-driven mappers and a small, pluggable engine.

At its core Remap lets you declare how target classes should receive data, and then automatically builds target instances from arbitrary sources. Mappings are driven by the receiving (target) class — not by the source — which keeps mapping logic colocated with the destination data shape and removes authority from the input objects.

Key features:

- Automatic object-to-object mapping using attribute-based mappers and casters.
- Bindings that associate source types (class names or scalar types like `type:array`) to target classes or to dynamic resolver callables.
- Dynamic resolvers: a binding may be a callable that inspects the source and context to pick the appropriate target type at runtime.
- Recursive-safe mapping with a registry to preserve reference cycles and avoid infinite loops.
- Small, extensible cast system (DateTime, default values, iterables, nested object transformers) so outputs can be shaped precisely.

Remap is useful when you want a clear separation between input data and output models, and prefer to centralize mapping logic in the classes that consume the data rather than in the producers.

## Installation

Install Remap via Composer. From your project root run:

```bash
composer require luimedi/remap
```

## Usage

### Basic Usage

Below is a minimal demonstration showing how a source class `BookEntity` can be mapped to a target `BookResource`. The example uses `CastDateTime` to convert a publication `DateTime` into an ISO-8601 string on the output.

```php
<?php

use Luimedi\Remap\Mapper;
use Luimedi\Remap\Attribute\ConstructorMapper;
use Luimedi\Remap\Attribute\MapProperty;
use Luimedi\Remap\Attribute\Cast\CastDateTime;

// Source entity coming from anywhere (DB, API, etc.)
class BookEntity
{
	public function __construct(
		public string $title,
		public string $author,
		public DateTimeInterface $publishedAt
	) {}
}

// Target resource declares how it wants its data. Mapping logic lives here.
#[ConstructorMapper]
class BookResource
{
	public function __construct(
		#[MapProperty(source: 'title')]
		public string $title,

		#[MapProperty(source: 'author')]
		public string $author,

		// Cast the DateTime to an ISO-8601 string for transport
		#[MapProperty(source: 'publishedAt')]
		#[CastDateTime]
		public string $publishedAt
	) {}
}

// Usage: bind the source type to the target and map instances
$mapper = new Mapper();
$mapper->bind(BookEntity::class, BookResource::class);

$entity = new BookEntity(
	title: 'El Hobbit',
	author: 'J.R.R. Tolkien',
	publishedAt: new DateTimeImmutable('1937-09-21')
);

$resource = $mapper->map($entity);

// $resource is an instance of BookResource with publishedAt as an ISO string
echo $resource->title; // 'El Hobbit'
echo $resource->publishedAt; // '1937-09-21T00:00:00+00:00'
```

This example shows the guiding principle of Remap: the target type defines how data should be extracted and transformed from arbitrary sources, keeping mapping responsibility on the receiver side.

### ConstructorMapper

`ConstructorMapper` instructs Remap to build the target type by mapping constructor
parameters. Each constructor parameter can be annotated with `MapProperty`/`MapGetter`
and casting attributes so the constructor receives already-transformed values. This
is particularly useful for immutable or value objects where dependencies are
declared in the constructor.

Example (conceptual):

```php
#[ConstructorMapper]
class UserResource
{
	public function __construct(
		#[MapProperty(source: 'username')]
		public string $username,

		#[MapProperty(source: 'registeredAt')]
		#[CastDateTime]
		public string $registeredAt
	) {}
}
```

When `Engine` executes mapping for a class annotated with `ConstructorMapper`, it
collects parameter values using the provided mapping attributes, applies casters,
and calls the constructor (or populates an existing placeholder instance for
recursive scenarios).

### PropertyMapper

`PropertyMapper` maps public properties on the target class. It inspects each
public property for `MapProperty`/`MapGetter` and cast attributes and assigns the
resulting value directly onto the property. This is a convenient approach for
mutable DTOs or when you prefer property-based initialization.

Example:

```php
#[PropertyMapper]
class ArticleResource
{
	#[MapProperty(source: 'title')]
	public string $title;

	#[MapProperty(source: 'body')]
	public string $body;

	#[MapProperty(source: 'publishedAt')]
	#[CastDateTime]
	public string $publishedAt;
}
```

### Using Both Together

`ConstructorMapper` and `PropertyMapper` can be combined on the same class. When
both are present, the engine will run any `TransformerInterface`-based attributes
(like `ConstructorMapper`) first to create the initial instance, and `PropertyMapper`
will then assign public properties. This allows you to use constructor mapping for
core, required values and property mapping for optional or later-assigned fields.

Using them together gives you flexible control over how instances are created and
populated while keeping mapping logic in the target class.

### MapProperty and MapGetter

`MapProperty` and `MapGetter` are the primary mapping attributes used to extract
values from the source when populating a target. They can be applied to constructor
parameters or public properties.

- `MapProperty`: reads a value from the source using dot-notation (e.g. `user.name`).
	It works with arrays and objects — internally it uses the `Data::get()` helper
	to traverse nested structures and access properties or array keys.

- `MapGetter`: calls a method on the source object. Provide the method name and
	the attribute will call that getter on the source (e.g. `getType`). This is useful
	when the source exposes computed values or an explicit API for retrieving a value.

Examples:

```php
<?php
use Luimedi\Remap\Mapper;
use Luimedi\Remap\Attribute\PropertyMapper;
use Luimedi\Remap\Attribute\MapProperty;
use Luimedi\Remap\Attribute\MapGetter;

// Source object with nested author data and a getter method
class BookEntityExample
{
	public array $author = ['name' => 'J.R.R. Tolkien'];

	public function getType(): string
	{
		return 'fiction';
	}
}

// Target uses PropertyMapper to declare how to read fields
#[PropertyMapper]
class BookTargetExample
{
	#[MapProperty(source: 'author.name')]
	public string $authorName;

	#[MapGetter(source: 'getType')]
	public string $type;
}

// Usage
$mapper = new Mapper();
$mapper->bind(BookEntityExample::class, BookTargetExample::class);

$source = new BookEntityExample();
$target = $mapper->map($source);

echo $target->authorName; // 'J.R.R. Tolkien'
echo $target->type; // 'fiction'
```

Casting and order of operations:

- When both mapping attributes and cast attributes are present, mapping occurs first
	and the resulting value is then passed to the caster. This ensures that casters
	operate on the extracted value (for example, a `DateTime` instance) rather than
	on the raw source container.
- The `PropertyMapper` explicitly sorts attributes so that `MapInterface` implementations
	(like `MapProperty` and `MapGetter`) run before `CastInterface` implementations.

Notes and tips:

- `MapProperty` supports nested keys via dot-notation and works with arrays and
	objects; if a segment is missing the default `null` (or provided default) will be returned.
- `MapGetter` requires a callable method on the source — if the method does not
	exist a PHP exception will occur, so prefer checks or defensive code on the source
	or use a resolver to choose a safer mapping strategy.
- Use `MapGetter` for computed values and `MapProperty` for direct data lookups.

### Casters

Casters implement `CastInterface` and are responsible for transforming mapped
values into the shape expected by the target. They are applied after a value is
extracted (map -> cast). Two simple built-in casters are `CastDateTime` and
`CastDefault`.

- `CastDateTime`

	- Accepts a `DateTimeInterface` or a date string and returns an ISO-8601
		formatted string (DateTime::ATOM). If the input is not a date or string,
		it returns `null`.

	- Use it when your target expects a textual date representation instead of a
		`DateTime` object.

	Example:

	```php
	#[MapProperty(source: 'publishedAt')]
	#[CastDateTime]
	public string $publishedAt;
	```

- `CastDefault`

	- Provides a default value when the mapped value is empty or `null`.
	- It accepts a `$default` and a `$strict` flag. When `strict` is `true`, the
		default replaces only `null`; when `false`, empty values (null, `''`, `0`,
		`false`) are replaced.

	Example:

	```php
	#[MapProperty(source: 'nickname')]
	#[CastDefault(default: 'anonymous')]
	public string $nickname;
	```

Creating custom casters is straightforward — implement `CastInterface` and
register your logic in `cast(mixed $value, ContextInterface $context): mixed`. As
the library evolves more caster helpers will be added, but custom casters let you
tailor mapping behavior to your application's needs.

### CastTransformer (object/array transformation)

`CastTransformer` is a caster designed to map nested arrays and objects by
delegating those nested values back to the mapping engine. Use it when a field
contains an object or a structured array that should be converted into another
target type.

Principles and behavior:

- If the input is `null`, the caster returns `null`.
- Scalar values (string/int/float/bool) are returned unchanged — this caster
	focuses on arrays and objects.
- When a nested object has already been mapped earlier in the process, the
	caster will reuse the existing mapped instance. This preserves object
	identity and keeps cycles intact.
- The caster guards against infinite recursion when mapping structures that
	reference their parents; in such recursion cases it will return the already-
	mapped instance when available or `null` if a mapped value is not yet present.
- Under normal (non-recursive) conditions it will resolve the appropriate
	target type for the nested value and execute mapping so the nested structure
	becomes a mapped resource.

Example usage (mapping nested objects):

```php
<?php
use Luimedi\Remap\Mapper;
use Luimedi\Remap\Attribute\ConstructorMapper;
use Luimedi\Remap\Attribute\MapProperty;
use Luimedi\Remap\Attribute\Cast\CastTransformer;

// Simple source object with a nested parent
class NodeInput
{
	public ?NodeInput $parent = null;

	public function __construct(public string $name, ?NodeInput $parent = null)
	{
		$this->parent = $parent;
	}
}

#[ConstructorMapper]
class NodeResource
{
	public function __construct(
		#[MapProperty(source: 'name')]
		public string $name,

		#[MapProperty(source: 'parent')]
		#[CastTransformer]
		public ?NodeResource $parent = null
	) {}
}

// Usage: create cyclic or nested input and map
$parent = new NodeInput('root');
$child = new NodeInput('child', $parent);
$parent->parent = null; // keep it simple (no cycle here)

$mapper = new Mapper();
$mapper->bind(NodeInput::class, NodeResource::class);

$mapped = $mapper->map($child);

echo $mapped->name; // 'child'
echo $mapped->parent->name; // 'root'
```

### CastIterable

`CastIterable` is a small helper caster that applies another caster to each item
in an iterable. You provide the caster class name and optional constructor
arguments; `CastIterable` instantiates that caster and calls `cast()` for every
element in the input.

Key notes:

- `CastIterable` assumes the input is iterable. If you pass a non-iterable value
	a PHP warning/error will occur when attempting to `foreach` it — prefer to
	ensure the source provides an array or use `CastDefault` to supply a fallback.
- Because it delegates to another caster class, you can combine it with
	`CastDateTime`, `CastTransformer`, or any custom caster you implement.

Example: casting an array of dates to ISO strings using `CastDateTime`:

```php
<?php
use Luimedi\Remap\Mapper;
use Luimedi\Remap\Attribute\ConstructorMapper;
use Luimedi\Remap\Attribute\MapProperty;
use Luimedi\Remap\Attribute\Cast\CastIterable;
use Luimedi\Remap\Attribute\Cast\CastDateTime;

class DatesInput
{
	public function __construct(public array $dates) {}
}

#[ConstructorMapper]
class DatesResource
{
	public function __construct(
		#[MapProperty(source: 'dates')]
		#[CastIterable(class: CastDateTime::class)]
		public array $dates
	) {}
}

$mapper = new Mapper();
$mapper->bind(DatesInput::class, DatesResource::class);

$input = new DatesInput([
	new DateTimeImmutable('2020-01-01'),
	new DateTimeImmutable('2021-02-02'),
]);

$res = $mapper->map($input);

echo $res->dates[0]; // '2020-01-01T00:00:00+00:00'
```

Example: casting an array of nested objects using `CastTransformer` via `CastIterable`:

```php
<?php
use Luimedi\Remap\Mapper;
use Luimedi\Remap\Attribute\PropertyMapper;
use Luimedi\Remap\Attribute\MapProperty;
use Luimedi\Remap\Attribute\Cast\CastIterable;
use Luimedi\Remap\Attribute\Cast\CastTransformer;

class ChildInput
{
	public function __construct(public string $name) {}
}

class ParentInput
{
	public function __construct(public array $children) {}
}

#[PropertyMapper]
class ChildResource
{
	#[MapProperty(source: 'name')]
	public string $name;
}

#[PropertyMapper]
class ParentResource
{
	#[MapProperty(source: 'children')]
	#[CastIterable(class: CastTransformer::class)]
	/** @var ChildResource[] */
	public array $children = [];
}

$mapper = new Mapper();
$mapper->bind(ChildInput::class, ChildResource::class);
$mapper->bind(ParentInput::class, ParentResource::class);

$parentIn = new ParentInput([
	new ChildInput('a'),
	new ChildInput('b'),
]);

$parentOut = $mapper->map($parentIn);

echo $parentOut->children[0]->name; // 'a'
```

This composition (`CastIterable` + `CastTransformer`) is a common pattern for
mapping arrays of nested objects into arrays of mapped resources.

## License and credits

- **License:** This project is released under the MIT License. See the `LICENSE` file for full terms.
- **Author / Credits:** Luis Medina.
- **Warranty:** This software is provided free of charge and without any warranty. Use at your own risk — the software is provided "AS IS", without warranty of any kind, express or implied.
