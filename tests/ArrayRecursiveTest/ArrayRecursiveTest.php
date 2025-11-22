<?php

namespace Tests\ArrayRecursiveTest;

use PHPUnit\Framework\TestCase;
use Luimedi\Remap\Mapper;

class ArrayRecursiveTest extends TestCase
{
    public function testArrayRecursiveMappingPreservesCycles()
    {
        $parent = new ArrayInput('parent');
        $child = new ArrayInput('child', $parent);
        $parent->children = [$child];
        $child->parent = $parent; // create cycle

        $mapper = new Mapper();
        $mapper->bind(ArrayInput::class, ArrayOutput::class);

        $result = $mapper->map($parent);

        $this->assertInstanceOf(ArrayOutput::class, $result);
        $this->assertSame('parent', $result->name);

        $this->assertIsArray($result->children);
        $this->assertCount(1, $result->children);
        $childOut = $result->children[0];

        $this->assertInstanceOf(ArrayOutput::class, $childOut);
        $this->assertSame('child', $childOut->name);

        // Child's parent must point to the mapped parent (cycle preserved)
        $this->assertSame($result, $childOut->parent);
    }
}
