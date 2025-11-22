<?php

use PHPUnit\Framework\TestCase;
use Luimedi\Remap\Mapper;
use Tests\Demo\RecursiveInput;
use Tests\Demo\RecursiveOutputProp;

class RecursivePropertyTest extends TestCase
{
    public function testPropertyTransformerHandlesRecursiveReferences()
    {
        $child = new RecursiveInput('child');
        $parent = new RecursiveInput('parent', $child);
        $child->parent = $parent; // create cycle

        $mapper = new Mapper();
        $mapper->bind(RecursiveInput::class, RecursiveOutputProp::class);

        $result = $mapper->map($child);

        $this->assertInstanceOf(RecursiveOutputProp::class, $result);
        $this->assertEquals('child', $result->name);

        $this->assertInstanceOf(RecursiveOutputProp::class, $result->parent);
        $this->assertEquals('parent', $result->parent->name);

        // The parent's parent should reference the mapped child instance (cycle preserved)
        $this->assertSame($result, $result->parent->parent);
    }
}
