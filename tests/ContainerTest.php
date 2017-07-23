<?php

namespace Tests;

use \AdService\Container;
use \DateTime;
use \DateTimezone;
use \PHPUnit\Framework\TestCase;

 /**
  * @runTestsInSeparateProcesses
  */

class ContainerTest extends TestCase
    {

	/**
	 * Prepare data for testing
	 *
	 * @return void
	 */

	public function setUp()
	    {
		$c = new Container("anyname");
		$c->clear();
		$c = new Container("anyname_1");
		$c->clear();
		$c = new Container("anyname_2");
		$c->clear();
		$c = new Container("anyname_3");
		$c->clear();
		$c = new Container("anyname_4");
		$c->clear();
		$c = new Container("anyname_5");
		$c->clear();
		$c = new Container("anyname_6");
		$c->clear();

		parent::setUp();
	    } //end setUp()


	/**
	 * Destroy testing data
	 *
	 * @return void
	 */

	public function tearDown()
	    {
		$c = new Container("anyname");
		$c->clear();
		$c = new Container("anyname_1");
		$c->clear();
		$c = new Container("anyname_2");
		$c->clear();
		$c = new Container("anyname_3");
		$c->clear();
		$c = new Container("anyname_4");
		$c->clear();
		$c = new Container("anyname_5");
		$c->clear();
		$c = new Container("anyname_6");
		$c->clear();

		parent::tearDown();
	    } //end setUp()


	/**
	 * Should add element
	 *
	 * @return void
	 */

	public function testShouldAddElement()
	    {
		$container = new Container("anyname");
		$this->assertTrue($container->add("first"));
		$this->assertTrue($container->add("second"));
		$expected = array("first", "second");

		$this->assertEquals(2, count($container));
		$i = 0;
		foreach ($container as $key => $element)
		    {
			$now = new DateTime("now", new DateTimezone("UTC"));
			$this->assertEquals($expected[$i], $element["data"]);
			$this->assertEquals($now, $element["creation_time"]);
			$this->assertEquals(40, strlen($element["id"]));
			$this->assertEquals($i, $key);
			$container->remove($key);
			$i++;
		    }

		$this->assertEquals(0, count($container));
		
	    } //end testShouldAddElement()


	/**
	 * Should clear container
	 *
	 * @return void
	 */

	public function testShouldClearContainer()
	    {
		$container = new Container("anyname");

		$xml = file_get_contents(__DIR__ . "/datasets/xml/1");

		for ($i = 0; $i < 500; $i++)
		    {
			$this->assertTrue($container->add($xml));
		    }

		$this->assertEquals(500, count($container));
		$container = new Container("anyname");
		$this->assertEquals(500, count($container));
		$container->clear();
		$this->assertEquals(0, count($container));
	    } //end testShouldClearContainer()


	/**
	 * Should move elements to parallels if it needed
	 *
	 * @return void
	 */

	public function testShouldMoveElementsToParallelsIfItNeeded()
	    {
		$container = new Container("anyname", 6);

		for ($i = 0; $i < 12; $i++)
		    {
			$container->add("data" . $i);
		    }

		$container = new Container("anyname_0");
		$this->assertEquals(0, count($container));

		$container = new Container("anyname_7");
		$this->assertEquals(0, count($container));

		for ($i = 1; $i <= 6; $i++)
		    {
			$container = new Container("anyname_" . $i);
			$this->assertEquals(2, count($container));
		    }

	    } //end testShouldMoveElementsToParallelsIfItNeeded()


	/**
	 * Should have constant container_dir
	 *
	 * @rerurn void
	 */

	public function testShouldHaveConstantContainerDir()
	    {
		define("CONTAINER_DIR", __DIR__ . "/container");
		$container = new Container("anyname");

		for ($i = 0; $i < 12; $i++)
		    {
			$container->add("data" . $i);
		    }

		foreach ($container as $element)
		    {
			$this->assertTrue(file_exists(__DIR__ . "/container/" . $element["container"] . "/" . $element["id"]));
		    } //end foreach

	    } //end testShouldHaveConstantContainerDir()


    } //end class

?>
