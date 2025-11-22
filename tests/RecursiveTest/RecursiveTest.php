<?php

namespace Tests\RecursiveTest;

use PHPUnit\Framework\TestCase;
use Luimedi\Remap\Mapper;

class RecursiveTest extends TestCase
{
    public function testRecursiveMappingDoesNotLoop()
    {
        // Build a recursive input: child -> parent -> null, but parent references back to child to create a loop
        $child = new RecursiveInput('child');
        $parent = new RecursiveInput('parent', $child);
        // create cycle
        $child->parent = $parent;

        $mapper = new Mapper();
        // Bind the input class to the output class
        $mapper->bind(RecursiveInput::class, RecursiveOutput::class);

        $result = $mapper->map($child);

        $this->assertInstanceOf(RecursiveOutput::class, $result);
        $this->assertEquals('child', $result->name);

        // Parent should be mapped, but its parent reference should not infinitely recurse.
        $this->assertInstanceOf(RecursiveOutput::class, $result->parent);
        $this->assertEquals('parent', $result->parent->name);

        // Because of the guard, the grand-parent (which would point back to child) should be left as the original source object
        // or remain unmapped (in our current implementation the guard returns the original value), so ensure we don't get infinite loop
        $this->assertTrue(
            $result->parent->parent === $child || $result->parent->parent === null || $result->parent->parent instanceof RecursiveOutput
        );
    }
}
