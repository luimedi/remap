<?php

namespace Luimedi\Remap;

class DataTest extends \PHPUnit\Framework\TestCase
{
    public function testGetData()
    {
        $data = [
            'user' => [
                'name' => 'Alice',
                'address' => [
                    'city' => 'Wonderland'
                ]
            ]
        ];

        $city = Data::get($data, 'user.address.city');
        $this->assertEquals('Wonderland', $city);

        $name = Data::get($data, 'user.name');
        $this->assertEquals('Alice', $name);

        $nonExistent = Data::get($data, 'user.age', 30);
        $this->assertEquals(30, $nonExistent);

        $this->assertSame($data['user']['address']['city'], 'Wonderland');
    }
}