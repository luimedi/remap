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

## License and credits

- **License:** This project is released under the MIT License. See the `LICENSE` file for full terms.
- **Author / Credits:** Luis Medina.
- **Warranty:** This software is provided free of charge and without any warranty. Use at your own risk — the software is provided "AS IS", without warranty of any kind, express or implied.
