<?php

namespace Tests;

use Luimedi\Remap\Helpers\Data;

class DataTest extends \PHPUnit\Framework\TestCase
{
    public function testGetData()
    {
        $data = [
            'user' => [
                'name' => 'Alice',
                'address' => [
                    'city' => 'Wonderland',
                ],
            ],
        ];

        $city = Data::get($data, 'user.address.city');
        $this->assertEquals('Wonderland', $city);

        $name = Data::get($data, 'user.name');
        $this->assertEquals('Alice', $name);

        $nonExistent = Data::get($data, 'user.age', 30);
        $this->assertEquals(30, $nonExistent);

        $this->assertSame($data['user']['address']['city'], 'Wonderland');
    }

    public function testGetWithPrivateAndProtectedProperties()
    {
        $obj = new TestDataClass();
        $this->assertSame('private_value', Data::get($obj, 'privateProp'));
        $this->assertSame('protected_value', Data::get($obj, 'protectedProp'));
        $this->assertSame('public_value', Data::get($obj, 'publicProp'));
    }

    public function testGetWithUninitializedProperty()
    {
        $obj = new TestDataClass();
        $this->assertSame('default_val', Data::get($obj, 'uninitializedProp', 'default_val'));
    }

    public function testGetWithMagicMethod()
    {
        $obj = new TestDataClass();
        $this->assertSame('magic_value', Data::get($obj, 'magic'));
        $this->assertSame('fallback', Data::get($obj, 'non_existent_magic', 'fallback'));
    }

    public function testGetWithArrayAccess()
    {
        $obj = new TestArrayAccess(['nested' => ['key' => 'val']]);
        $this->assertSame('val', Data::get($obj, 'nested.key'));
    }
}

class TestDataClass
{
    private string $privateProp = 'private_value';
    protected string $protectedProp = 'protected_value';
    public string $uninitializedProp;
    public string $publicProp = 'public_value';
    private array $dynamic = ['magic' => 'magic_value'];

    public function __get($name)
    {
        return $this->dynamic[$name] ?? null;
    }
}

class TestArrayAccess implements \ArrayAccess
{
    private array $container = [];

    public function __construct(array $data)
    {
        $this->container = $data;
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->container);
    }

    public function offsetGet($offset): mixed
    {
        return $this->container[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        $this->container[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->container[$offset]);
    }
}
