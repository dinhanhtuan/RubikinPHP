<?php
namespace www\week2\day1\dinhtuan

class MemoryCacheTest extends PHPUnit_Framework_TestCase
{
	public $testCache;
	public function setUp()
	{
		$this->testCache = new MemoryCache();
	}

	public function testAdd()
	{
		$this->testCache->add('key', 'value');
		$this->assertArrayHasKey('key', $this->testCache->cacheArray);
	}
}