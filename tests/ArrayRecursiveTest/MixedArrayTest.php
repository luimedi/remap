<?php

namespace Tests\ArrayRecursiveTest;

use PHPUnit\Framework\TestCase;
use Luimedi\Remap\Mapper;

class MixedArrayTest extends TestCase
{
    public function testMixedArrayElementsAreHandledCorrectly()
    {
        $parent = new ArrayInput('parent');
        $child = new ArrayInput('child', $parent);
        $parent->children = ['note', $child];
        $child->parent = $parent;

        $mapper = new Mapper();
        $mapper->bind(ArrayInput::class, ArrayOutput::class);

        $result = $mapper->map($parent);

        $this->assertInstanceOf(ArrayOutput::class, $result);
        $this->assertSame('parent', $result->name);

        $this->assertIsArray($result->children);
        $this->assertCount(2, $result->children);

        $this->assertSame('note', $result->children[0]);

        $childOut = $result->children[1];
        $this->assertInstanceOf(ArrayOutput::class, $childOut);
        $this->assertSame('child', $childOut->name);

        // Ensure recursion preserved: child's parent points to mapped parent
        $this->assertSame($result, $childOut->parent);
    }
}
