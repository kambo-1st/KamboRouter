<?php
namespace Kambo\Tests\Router\Enum;

use Kambo\Router\Enum\Enum;

/**
 * Test for Enum class
 *
 * @package Test
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license Apache-2.0
 */
class EnumTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test toArray method
     *
     * @return void
     */
    public function testToArray()
    {
        $expected = [
            'FOO' => 'foo_value',
            'BAR' => 'bar_value',
        ];

        $this->assertEquals($expected, TestEnum::toArray());
    }

    /**
     * Test Values method
     *
     * @return void
     */
    public function testValues()
    {
        $expected = [
            'FOO' => 'foo_value',
            'BAR' => 'bar_value',
        ];

        $this->assertEquals($expected, TestEnum::values());
    }
}

class TestEnum extends Enum
{
    const FOO = 'foo_value';
    const BAR = 'bar_value';
}
