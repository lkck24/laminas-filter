<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\RealPath as RealPathFilter;
use PHPUnit\Framework\TestCase;
use stdClass;

use function strpos;

use const DIRECTORY_SEPARATOR;
use const PHP_OS;

class RealPathTest extends TestCase
{
    // @codingStandardsIgnoreStart
    /**
     * Path to test files
     *
     * @var string
     */
    protected $_filesPath;

    /**
     * Laminas_Filter_Basename object
     *
     * @var RealPathFilter
     */
    protected $_filter;
    // @codingStandardsIgnoreEnd

    /**
     * Creates a new Laminas_Filter_Basename object for each test method
     */
    public function setUp(): void
    {
        $this->_filesPath = __DIR__ . DIRECTORY_SEPARATOR . '_files';
        $this->_filter    = new RealPathFilter();
    }

    /**
     * Ensures expected behavior for existing file
     *
     * @return void
     */
    public function testFileExists()
    {
        $filter   = $this->_filter;
        $filename = 'file.1';
        $this->assertStringContainsString($filename, $filter($this->_filesPath . DIRECTORY_SEPARATOR . $filename));
    }

    /**
     * Ensures expected behavior for nonexistent file
     *
     * @return void
     */
    public function testFileNonexistent()
    {
        $filter = $this->_filter;
        $path   = '/path/to/nonexistent';
        if (false !== strpos(PHP_OS, 'BSD')) {
            $this->assertEquals($path, $filter($path));
        } else {
            $this->assertEquals(false, $filter($path));
        }
    }

    /**
     * @return void
     */
    public function testGetAndSetExistsParameter()
    {
        $this->assertTrue($this->_filter->getExists());
        $this->_filter->setExists(false);
        $this->assertFalse($this->_filter->getExists());

        $this->_filter->setExists(['unknown']);
        $this->assertTrue($this->_filter->getExists());
    }

    /**
     * @return void
     */
    public function testNonExistantPath()
    {
        $filter = $this->_filter;
        $filter->setExists(false);

        $path = __DIR__ . DIRECTORY_SEPARATOR . '_files';
        $this->assertEquals($path, $filter($path));

        $path2 = __DIR__ . DIRECTORY_SEPARATOR . '_files'
               . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '_files';
        $this->assertEquals($path, $filter($path2));

        $path3 = __DIR__ . DIRECTORY_SEPARATOR . '_files'
               . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '.'
               . DIRECTORY_SEPARATOR . '_files';
        $this->assertEquals($path, $filter($path3));
    }

    public function returnUnfilteredDataProvider(): array
    {
        return [
            [null],
            [new stdClass()],
            [
                [
                    $this->_filesPath . DIRECTORY_SEPARATOR . 'file.1',
                    $this->_filesPath . DIRECTORY_SEPARATOR . 'file.2',
                ],
            ],
        ];
    }

    /**
     * @dataProvider returnUnfilteredDataProvider
     * @param null | stdClass | string $input
     * @return void
     */
    public function testReturnUnfiltered($input)
    {
        $this->assertEquals($input, $this->_filter->filter($input));
    }
}
