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
register your logic in `cast(mixed $value, ContextInterface $context): mixed`.
As the library evolves more caster helpers will be added, but custom casters let
you tailor mapping behavior to your application's needs.

#### Creating custom casters

Below is a complete example showing how to implement a tiny custom caster that
appends " Aoy!" to a string, and how to use it in a constructor-mapped target.

```php
<?php

namespace App\Cast;

use Attribute;
use Luimedi\Remap\Attribute\Cast\CastInterface;
use Luimedi\Remap\ContextInterface;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class AppendAoy implements CastInterface
{
	public function __construct()
	{
	}

	public function cast(mixed $value, ContextInterface $context): mixed
	{
		if ($value === null) {
			return null;
		}

		return (string)$value . ' Aoy!';
	}
}
```

Usage example (constructor-mapped target):

```php
<?php

use Luimedi\Remap\Mapper;
use Luimedi\Remap\Attribute\ConstructorMapper;
use Luimedi\Remap\Attribute\MapProperty;

class MessageInput
{
	public function __construct(public string $text) {}
}

#[ConstructorMapper]
class MessageResource
{
	public function __construct(
		#[MapProperty(source: 'text')]
		#[\App\Cast\AppendAoy]
		public string $text
	) {}
}

$mapper = new Mapper();
$mapper->bind(MessageInput::class, MessageResource::class);

$out = $mapper->map(new MessageInput('Hello'));
echo $out->text; // "Hello Aoy!"
```

Notes:

- Keep casters small and deterministic — they should not perform side effects.
- Caster attributes may accept constructor arguments if you need configurable
  behavior (for example a suffix or formatting options).
- Because casters run after mapping, they receive the extracted value (not the
  source container), so they can assume they're operating on the relevant
  primitive/object to transform.

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

## Advanced

### Context and Dynamic Binding

`Context` is a key/value bag used during mapping to carry auxiliary
information that resolvers and casters can consult at runtime. It is useful to
pass flags, configuration or external services into the mapping process without
polluting source objects.

How to set context:

- When you create a `Mapper`, you can add global context entries using
	`withContext($key, $value)` on the mapper instance. Those values are available
	to all mappings performed by that mapper.
- When you call `map($from, array $data = [])`, the mapper merges the mapper's
	context with the provided `$data` to create a mapping-local `Context` instance.
	This is handy to pass per-call options.

Why use Context:

- Allow dynamic binding decisions depending on runtime information (for
	example user role, feature flags, or external configuration).
- Provide services to casters or resolvers without coupling them to global
	singletons — pass service references via context if needed.

Dynamic binding example (resolver reads the context):

```php
<?php
use Luimedi\Remap\Mapper;

// Source type
class UserInput
{
		public function __construct(public string $name, public string $role) {}
		public function isAdmin(): bool { return $this->role === 'admin'; }
}

// Target types
class AdminResource { public string $name; public string $level = 'admin'; }
class UserResource  { public string $name; public string $level = 'user'; }

$mapper = new Mapper();

// Bind the source class to a resolver callable that inspects context
$mapper->bind(UserInput::class, function($from, $context) {
		// resolver can consult the source object or the context values
		if ($from->isAdmin() || $context->get('force_admin') === true) {
				return AdminResource::class;
		}
		return UserResource::class;
});

// Option A: use the source's own data
$res1 = $mapper->map(new UserInput('Alice', 'admin'));

// Option B: override decision via context
$mapper->withContext('force_admin', true);
$res2 = $mapper->map(new UserInput('Bob', 'user'));

// res1 will be AdminResource (because isAdmin()), res2 will be AdminResource
// because we set the 'force_admin' flag in the context.
```

Notes on safety and scope:

- Context values are shallow-copied into a new `Context` per `map()` call so
	per-call data does not leak across invocations.
- Avoid storing large objects in context unless necessary; prefer service
	factories or lightweight references.

Combining dynamic binding with casters

Resolvers often work hand-in-hand with casters like `CastTransformer`. A
resolver can choose the appropriate target type based on the context, and the
caster will then map nested structures into that chosen type. This enables
flexible polymorphic mapping driven by runtime conditions.

### Custom TransformerInterface implementations

`TransformerInterface` allows you to write attribute-based transformers that take
full control of how a target instance is produced from a source. Unlike simple
mapping attributes that extract values for a single parameter or property, a
transformer runs at the class level and can build, replace or mutate the
entire target object.

When to use a custom transformer:

- Complex construction logic that can't be expressed with parameter/property
	mappings alone.
- Performing side-effect-free transformations (enriching, normalizing,
	aggregating) before the usual property/constructor mapping runs.
- Interoperating with legacy objects or 3rd-party APIs that require special
	instantiation logic.

Transformer lifecycle (high level):

- The engine discovers class-level attributes and invokes any transformers
	before completing the mapping process. A transformer receives the source and
	either the current target instance (if a placeholder exists) or the target
	type name. The transformer must return a concrete instance of the target.
- Because transformers run early, they can create a fully-initialized instance
	that subsequent mappers or property assignments will respect.

Example: a custom transformer attribute that builds a target and sets extra data

```php
<?php
namespace App\Mapping;

use Attribute;
use Luimedi\Remap\Attribute\TransformerInterface;
use Luimedi\Remap\ContextInterface;

#[Attribute(Attribute::TARGET_CLASS)]
class ExampleTransformer implements TransformerInterface
{
		public function transform(mixed $source, mixed $target, ContextInterface $context): mixed
		{
				// $target may be a class name (string) or an existing instance.
				$class = is_string($target) ? $target : get_class($target);

				// Create a new instance using a simple constructor or custom logic.
				$instance = new $class();

				// Populate instance from source using custom rules
				if (is_object($source) && property_exists($source, 'meta')) {
						$instance->meta = strtoupper((string)$source->meta);
				}

				// Return the final instance for the engine to continue with.
				return $instance;
		}
}
```

Usage on a target class:

```php
#[\App\Mapping\ExampleTransformer]
class ProductResource
{
		public string $sku;
		public string $meta;
}

$mapper = new \Luimedi\Remap\Mapper();
$mapper->bind(ProductInput::class, ProductResource::class);

$out = $mapper->map(new ProductInput(...));
```

Notes and best practices:

- Always return a concrete instance of the expected target type.
- Keep transformers deterministic and side-effect free — they should not rely on
	global mutable state.
- Transformers can cooperate with `ConstructorMapper` and `PropertyMapper`:
	because transformers run early you can create a base instance and let the
	other mappers fill remaining fields, or you may return a complete instance
	and skip further mapping if desired.

With `TransformerInterface` you gain full control over the instantiation phase
and can implement advanced mapping rules while still leveraging the rest of the
attribute-driven system.

## License and credits

- **License:** This project is released under the MIT License. See the `LICENSE` file for full terms.
- **Author / Credits:** Luis Medina.
- **Warranty:** This software is provided free of charge and without any warranty. Use at your own risk — the software is provided "AS IS", without warranty of any kind, express or implied.
