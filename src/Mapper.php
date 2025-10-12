<?php

namespace Luimedi\Remap;

use InvalidArgumentException;

class Mapper 
{
    /**
     * @var array<string, string|callable>
     */
    protected array $bindings = [];

    /**
     * This holds contextual information for the mapping process.
     * Is passed as argument to resolvers and mappers.
     */
    protected Context $context;

    /**
     * The engine responsible for executing the mapping reading 
     * the attributes from the target class.
     */
    protected Engine $engine;

    /**
     * Initializes the Mapper with an optional context.
     */
    public function __construct(?Context $context = null, ?Engine $engine = null)
    {
        $this->context = $context ?? new Context();
        $this->engine = $engine ?? $this->getDefaultEngine();
    }

    /**
     * Binds a source type to a target type or a resolver function.
     * 
     * @param string $abstract The source type (class name or 'type:<type>').
     * @param string|callable($object, Context $context):string $resolver The target type (class name) or a resolver function.
     * 
     * @return $this
     */
    public function bind(string $abstract, string|callable $resolver): static
    {
        $this->bindings[$abstract] = $resolver;
        return $this;
    }

    /**
     * Adds a contextual key-value pair to be used during mapping.
     */
    public function withContext(string $key, mixed $value): static
    {
        $this->context->set($key, $value);
        return $this;
    }

    /**
     * Sets the entire context object.
     */
    public function setContext(Context $context): static
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Retrieves the current context.
     */
    public function getContext(): Context
    {
        return $this->context;
    }

    /**
     * Maps the given object to its target type.
     * 
     * @param mixed $from The source object to be mapped.
     * @param array $data Additional contextual data for this mapping operation.
     * 
     * @throws InvalidArgumentException if no binding is found or cannot be resolved.
     */
    public function map(mixed $from, array $data = []): mixed
    {
        $type = $this->resolve($from);
        $context = new Context(array_merge($this->context->all(), $data));

        return $this->engine->execute($from, $type, $context);
    }

    /**
     * Resolves the target type for the given object.
     * 
     * @throws InvalidArgumentException if no binding is found or cannot be resolved.
     */
    public function resolve(mixed $object): string
    {
        $type = get_class($object) ?: 'type:' . gettype($object);

        if (!isset($this->bindings[$type])) {
            throw new InvalidArgumentException("No binding found for {$type}");
        }

        $resolver = $this->bindings[$type];

        if (is_callable($resolver)) {
            return $resolver($object, $this->context);
        }

        if (class_exists($resolver)) {
            return $type;
        }

        throw new InvalidArgumentException(
            "Cannot resolve binding for {$type}");
    }

    /**
     * Provides a default engine instance.
     */
    public function getDefaultEngine(): Engine
    {
        return new Engine();
    }
}
