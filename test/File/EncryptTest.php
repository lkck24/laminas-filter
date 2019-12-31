<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */
namespace ZendTest\Filter\File;

use Zend\Filter\File\Encrypt as FileEncrypt;
use Zend\Filter\File\Decrypt as FileDecrypt;

/**
 * @group Zend_Filter
 */
class EncryptTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (! extension_loaded('mcrypt')) {
            $this->markTestSkipped('This filter needs the mcrypt extension');
        }

        if (file_exists(dirname(__DIR__) . '/_files/newencryption.txt')) {
            unlink(dirname(__DIR__) . '/_files/newencryption.txt');
        }
    }

    public function tearDown()
    {
        if (file_exists(dirname(__DIR__) . '/_files/newencryption.txt')) {
            unlink(dirname(__DIR__) . '/_files/newencryption.txt');
        }
    }

    /**
     * Ensures that the filter follows expected behavior
     *
     * @return void
     */
    public function testBasic()
    {
        $filter = new FileEncrypt();
        $filter->setFilename(dirname(__DIR__) . '/_files/newencryption.txt');

        $this->assertEquals(dirname(__DIR__) . '/_files/newencryption.txt', $filter->getFilename());

        $filter->setKey('1234567890123456');
        $this->assertEquals(dirname(__DIR__) . '/_files/newencryption.txt', $filter->filter(dirname(__DIR__) . '/_files/encryption.txt'));

        $this->assertEquals('Encryption', file_get_contents(dirname(__DIR__) . '/_files/encryption.txt'));

        $this->assertNotEquals('Encryption', file_get_contents(dirname(__DIR__) . '/_files/newencryption.txt'));
    }

    public function testEncryptionWithDecryption()
    {
        $filter = new FileEncrypt();
        $filter->setFilename(dirname(__DIR__) . '/_files/newencryption.txt');
        $filter->setKey('1234567890123456');
        $this->assertEquals(dirname(__DIR__) . '/_files/newencryption.txt', $filter->filter(dirname(__DIR__) . '/_files/encryption.txt'));

        $this->assertNotEquals('Encryption', file_get_contents(dirname(__DIR__) . '/_files/newencryption.txt'));

        $filter = new FileDecrypt();
        $filter->setKey('1234567890123456');
        $input = $filter->filter(dirname(__DIR__) . '/_files/newencryption.txt');
        $this->assertEquals(dirname(__DIR__) . '/_files/newencryption.txt', $input);

        $this->assertEquals('Encryption', trim(file_get_contents(dirname(__DIR__) . '/_files/newencryption.txt')));
    }

    /**
     *
     * @return void
     */
    public function testNonExistingFile()
    {
        $filter = new FileEncrypt();
        $filter->setKey('1234567890123456');

        $this->setExpectedException('\Zend\Filter\Exception\InvalidArgumentException', 'not found');
        echo $filter->filter(dirname(__DIR__) . '/_files/nofile.txt');
    }

    /**
     *
     * @return void
     */
    public function testEncryptionInSameFile()
    {
        $filter = new FileEncrypt();
        $filter->setKey('1234567890123456');

        copy(dirname(__DIR__) . '/_files/encryption.txt', dirname(__DIR__) . '/_files/newencryption.txt');
        $filter->filter(dirname(__DIR__) . '/_files/newencryption.txt');

        $this->assertNotEquals('Encryption', trim(file_get_contents(dirname(__DIR__) . '/_files/newencryption.txt')));
    }

    public function returnUnfilteredDataProvider()
    {
        return array(
            array(null),
            array(new \stdClass()),
            array(array(
                dirname(__DIR__) . '/_files/nofile.txt',
                dirname(__DIR__) . '/_files/nofile2.txt'
            ))
        );
    }

    /**
     * @dataProvider returnUnfilteredDataProvider
     * @return void
     */
    public function testReturnUnfiltered($input)
    {
        $filter = new FileEncrypt();
        $filter->setKey('1234567890123456');

        $this->assertEquals($input, $filter($input));
    }
}
