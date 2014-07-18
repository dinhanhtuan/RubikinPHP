<?php
namespace www\week2\day1\dinhtuan\test;

use www\week2\day1\dinhtuan\MemoryCache;

// Init & Register Autoloader
require "SplClassLoader.php";
$loader = new \SplClassLoader('www', 'D:\wamp');
$loader->register();

class MemoryCacheTest extends \PHPUnit_Framework_TestCase
{
	public $testCache;
	public function setUp()
	{
		$this->testCache = new MemoryCache();
		$this->testCache->add('exist_key', 'exist_value');
	}

	public function testAdd()
	{
		$this->assertEquals(array('exist_key' => 'exist_value'), \PHPUnit_Framework_Assert::readAttribute($this->testCache, 'cacheArray'));
		$this->testCache->add('new_key', 'new_value');
		$this->assertEquals(array( 'exist_key' => 'exist_value', 'new_key' => 'new_value'), \PHPUnit_Framework_Assert::readAttribute($this->testCache, 'cacheArray'));
	}

	public function testCheck_exist()
	{
		$this->assertFalse($this->testCache->check_exist('not_exist_key'));
		$this->assertTrue($this->testCache->check_exist('exist_key'));
	}

	public function testGet()
	{
		$this->assertEquals('exist_value', $this->testCache->get('exist_key'));
	}
}