<?php

namespace Luimedi\Remap;

use InvalidArgumentException;

class Mapper 
{
    /**
     * This holds contextual information for the mapping process.
     * Is passed as argument to resolvers and mappers.
     */
    protected ContextInterface $context;

    /**
     * The engine responsible for executing the mapping reading 
     * the attributes from the target class.
     */
    protected EngineInterface $engine;

    /**
     * Initializes the Mapper with an optional context.
     */
    public function __construct(?ContextInterface $context = null, ?EngineInterface $engine = null)
    {
        $this->engine = $engine ?? new Engine();
        $this->setContext($context ?? new Context());
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
        $this->engine->bind($abstract, $resolver);
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
    public function setContext(ContextInterface $context): static
    {
        $this->context = $context;
        $this->withContext('__engine__', $this->engine);

        return $this;
    }

    /**
     * Retrieves the current context.
     */
    public function getContext(): ContextInterface
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
        $type = $this->engine->resolve($from, $this->context);
        $context = new Context(array_merge($this->context->all(), $data));

        return $this->engine->execute($from, $type, $context);
    }
}
