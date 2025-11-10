
# Remap

Remap is a PHP library for flexible mapping of objects and data structures, allowing you to transform and adapt data between different classes or formats. Its architecture is based on the use of **Map** and **Caster** to define how data conversion is performed.

## Main Features

- **Object-to-object mapping**: Transforms instances of one class into another, useful for DTOs, entities, etc.
- **Custom property mapping**: Allows you to define how specific properties are assigned and transformed.
- **Value casting**: Transforms individual values or collections using casters.
- **Support for collections/iterables**: Maps entire arrays or iterables of objects.
- **Extensible via interfaces**: You can create your own Map and Caster by implementing the corresponding interfaces.

---

## Map: What is it and how does it work?

A **Map** defines how the properties of an input object are mapped to an output object. You can use the default maps or create your own by implementing `MapInterface`.

### Main included Maps:

- **MapProperty**: Maps a property from one object to another property, with the option to use a caster to transform the value.
- **MapGetter**: Allows mapping using a getter method instead of a direct property.
- **ConstructorMapper**: Allows mapping directly through the target object's constructor.

#### Basic Map usage example:

```php
$mapper = new Mapper();
$mapper->bind(Input::class, Output::class);
$result = $mapper->map(new Input(...));
```

You can customize the mapping by implementing your own Map and registering it.

---

## Caster: What is it and how does it work?

A **Caster** transforms a value from one type to another during the mapping process. They implement the `CastInterface`.
The caster are called after map

### Main included Casters:

- **CastDefault**: Performs simple type casting.
- **CastDateTime**: Converts `DateTimeInterface` objects to string (ISO 8601 by default).
- **CastIterable**: Applies a caster to each element of an iterable (array, Traversable, etc.).
- **CastTransformer**: Allows applying a full mapping to a nested value using another Mapper.

#### Caster usage example:

```php
use Luimedi\Remap\Attribute\MapProperty;
use Luimedi\Remap\Attribute\CastDateTime;

class Output {
    #[MapProperty('birthdate')]
    #[CastDateTime]
    public string $birthdate;
}
```

---

## CastTransformer: Nested object mapping

`CastTransformer` is a special caster that allows mapping a value using another Mapper, ideal for nested properties or sub-objects.

### When to use it?
- When a property is a complex object that also needs to be mapped.
- When you have arrays of objects and each one must be transformed.

#### Usage example:

```php
use Luimedi\Remap\Attribute\MapProperty;
use Luimedi\Remap\Attribute\CastTransformer;

class NestedOutput {
    #[MapProperty('child')]
    #[CastTransformer]
    public ChildOutput $child;
}
```

---

## CastIterable: Collection casting

`CastIterable` allows you to apply a caster to each element of an iterable (array, Collection, etc.). It is useful for transforming lists of objects or values.

### When to use it?
- When a property is an array of objects/values that must be transformed individually.

#### Usage example:

```php
use Luimedi\Remap\Attribute\MapProperty;
use Luimedi\Remap\Attribute\CastIterable;
use Luimedi\Remap\Attribute\CastDateTime;

class Output {
    #[MapProperty('dates')]
    #[CastIterable(class: CastDateTime)]
    public array $dates;
}
```

In this example, each element of the `dates` array will be transformed using `CastDateTime`.

---

## mapAsIterable: Mapping entire collections

The `mapAsIterable` method of the `Mapper` allows you to transform an entire array or iterable of input objects into output objects.

### Usage example:

```php
$inputs = [new Input(...), new Input(...)];
$results = $mapper->mapAsIterable($inputs);
// $results is an array of Output
```

---

## Complete example

```php
$mapper = new Mapper();
$mapper->bind(Input::class, Output::class);
$inputs = [new Input('Luis', new DateTimeImmutable('1988-01-01'))];
$results = $mapper->mapAsIterable($inputs);
```

---

## Extension and customization

You can create your own Maps and Casters by implementing the `MapInterface` and `CastInterface` interfaces, and then use them in your attributes or Mapper configuration.

---

## Credits

Developed by Luis Medina.
