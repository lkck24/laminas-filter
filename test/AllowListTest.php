<?php

namespace LaminasTest\Filter;

use Laminas\Filter\FilterPluginManager;
use Laminas\Filter\AllowList as AllowListFilter;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayObject;
use Laminas\Stdlib\Exception;
use PHPUnit\Framework\TestCase;

class AllowListTest extends TestCase
{
    public function testConstructorOptions()
    {
        $filter = new AllowListFilter([
            'list'    => ['test', 1],
            'strict'  => true,
        ]);

        $this->assertEquals(true, $filter->getStrict());
        $this->assertEquals(['test', 1], $filter->getList());
    }

    public function testConstructorDefaults()
    {
        $filter = new AllowListFilter();

        $this->assertEquals(false, $filter->getStrict());
        $this->assertEquals([], $filter->getList());
    }

    public function testWithPluginManager()
    {
        $pluginManager = new FilterPluginManager(new ServiceManager());
        $filter = $pluginManager->get('AllowList');

        $this->assertInstanceOf('Laminas\Filter\AllowList', $filter);
    }

    public function testNullListShouldThrowException()
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $filter = new AllowListFilter([
            'list' => null,
        ]);
    }

    public function testTraversableConvertsToArray()
    {
        $array = ['test', 1];
        $obj = new ArrayObject(['test', 1]);
        $filter = new AllowListFilter([
            'list' => $obj,
        ]);
        $this->assertEquals($array, $filter->getList());
    }

    public function testSetStrictShouldCastToBoolean()
    {
        $filter = new AllowListFilter([
            'strict' => 1
        ]);
        $this->assertSame(true, $filter->getStrict());
    }

    /**
     * @param mixed $value
     * @param bool  $expected
     * @dataProvider defaultTestProvider
     */
    public function testDefault($value, $expected)
    {
        $filter = new AllowListFilter();
        $this->assertSame($expected, $filter->filter($value));
    }

    /**
     * @param bool $strict
     * @param array $testData
     * @dataProvider listTestProvider
     */
    public function testList($strict, $list, $testData)
    {
        $filter = new AllowListFilter([
            'strict' => $strict,
            'list'   => $list,
        ]);
        foreach ($testData as $data) {
            list($value, $expected) = $data;
            $message = sprintf(
                '%s (%s) is not filtered as %s; type = %s',
                var_export($value, true),
                gettype($value),
                var_export($expected, true),
                $strict
            );
            $this->assertSame($expected, $filter->filter($value), $message);
        }
    }

    public static function defaultTestProvider()
    {
        return [
            ['test',   null],
            [0,        null],
            [0.1,      null],
            [[],  null],
            [null,     null],
        ];
    }

    public static function listTestProvider()
    {
        return [
            [
                true, //strict
                ['test', 0],
                [
                    ['test',   'test'],
                    [0,        0],
                    [null,     null],
                    [false,    null],
                    [0.0,      null],
                    [[],  null],
                ],
            ],
            [
                false, //not strict
                ['test', 0],
                [
                    ['test',   'test'],
                    [0,        0],
                    [null,     null],
                    [false,    false],
                    [0.0,      0.0],
                    [0.1,      null],
                    [[],  null],
                ],
            ],
        ];
    }
}
