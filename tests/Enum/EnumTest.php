<?php
namespace Test;

use Kambo\Router\Enum\Enum;

class EnumTest extends \PHPUnit_Framework_TestCase {

    /**
     * Test toArray method
     * 
     * @return void
     */
    public function testToArray() {
        $expected = [
            'FOO' => 'foo_value',
            'BAR' => 'bar_value',            
        ];

        $this->assertEquals($expected, testEnum::toArray());
    }

    /**
     * Test Values method
     * 
     * @return void
     */
    public function testValues() {
        $expected = [
            'FOO' => 'foo_value',
            'BAR' => 'bar_value',            
        ];

        $this->assertEquals($expected, testEnum::values());
    }    
}

class testEnum extends Enum {
    const FOO = 'foo_value';
    const BAR = 'bar_value';
}