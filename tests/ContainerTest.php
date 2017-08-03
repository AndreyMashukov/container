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


	/**
	 * Should allow make processes always with default toggle
	 *
	 * @return void
	 */

	public function testShouldAllowMakeProcessesAlwaysWithDefaultToggle()
	    {
		define("CONTAINER_DIR", __DIR__ . "/container");
		$container = new Container("anyname");

		for ($i = 0; $i < 12; $i++)
		    {
			$container->add("data" . $i);
		    }

		foreach ($container as $element)
		    {
			$time = time();
			$this->assertTrue(file_exists(__DIR__ . "/container/" . $element["container"] . "/" . $element["id"]));
			$secondtime = time();
			$this->assertEquals($time, $secondtime);
		    } //end foreach

	    } //end testShouldAllowMakeProcessesAlwaysWithDefaultToggle()


	/**
	 * Should allow make process as decide the CPU toggle
	 *
	 * @return void
	 */

	public function testShouldAllowMakeProcessAsDecideTheCpuToggle()
	    {
		define("CONTAINER_DIR", __DIR__ . "/container");
		define("TOGGLE", \Container\Toggles\CPUToggle::class);
		$container = new Container("anyname");

		for ($i = 0; $i < 12; $i++)
		    {
			$container->add("data" . $i);
		    }

		$load   = sys_getloadavg();
		$sum    = 0;
		foreach ($load as $value)
		    {
			$sum += $value;
		    } //end foreach

		$middle = $sum/3;

		if ($middle <= 15)
		    {
			$expected = 0;
		    }
		else if ($middle > 15 && $middle <= 30)
		    {
		$expected = 5;
		    }
		else if ($middle > 30 && $middle <= 50)
		    {
			$expected = 10;
		    }
		else
		    {
			$expected = 60;
		    } //end if

		$time = time();
		$exp  = $time + $expected;

		foreach ($container as $element)
		    {
			$this->assertTrue(file_exists(__DIR__ . "/container/" . $element["container"] . "/" . $element["id"]));
			$secondtime = time();
			break;
		    } //end foreach

		$this->assertEquals($exp, $secondtime);
	    } //end ShouldAllowMakeProcessAsDecideTheCpuToggle()


    } //end class

?>
