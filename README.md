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

## License and credits

- **License:** This project is released under the MIT License. See the `LICENSE` file for full terms.
- **Author / Credits:** Luis Medina.
- **Warranty:** This software is provided free of charge and without any warranty. Use at your own risk — the software is provided "AS IS", without warranty of any kind, express or implied.
