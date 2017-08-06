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

		$c = new Container("anyname_2");
		$c->clear();
		$c = new Container("anyname_2_1");
		$c->clear();
		$c = new Container("anyname_2_2");
		$c->clear();
		$c = new Container("anyname_2_3");
		$c->clear();
		$c = new Container("anyname_2_4");
		$c->clear();
		$c = new Container("anyname_2_5");
		$c->clear();
		$c = new Container("anyname_2_6");
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

		$c = new Container("anyname_2");
		$c->clear();
		$c = new Container("anyname_2_1");
		$c->clear();
		$c = new Container("anyname_2_2");
		$c->clear();
		$c = new Container("anyname_2_3");
		$c->clear();
		$c = new Container("anyname_2_4");
		$c->clear();
		$c = new Container("anyname_2_5");
		$c->clear();
		$c = new Container("anyname_2_6");
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
			$this->assertEquals($now->format("d-m-Y H:i"), $element["creation_time"]->format("d-m-Y H:i"));
			$this->assertEquals(40, strlen($element["id"]));
			$this->assertEquals($i, $key);
			$container->remove($key);
			$i++;
		    }

		$this->assertEquals(0, count($container));
		
	    } //end testShouldAddElement()


	/**
	 * Should allow to add order count limit
	 *
	 * @return void
	 */

	public function testShouldAllowToAddOrderCountLimit()
	    {
		$container = new Container("anyname", 1, 10);
		for ($i = 0; $i < 20; $i++)
		    {
			$this->assertTrue($container->add("first"));
		    } //end for

		$this->assertEquals(10, count($container));
		$i = 0;
		foreach ($container as $key => $element)
		    {
			$this->assertEquals(40, strlen($element["id"]));
			$i++;
		    }

		$this->assertEquals(10, $i);
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

		for ($i = 0; $i < 100; $i++)
		    {
			$this->assertTrue($container->add($xml));
		    }

		$this->assertEquals(100, count($container));
		$container = new Container("anyname");
		$this->assertEquals(100, count($container));
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
	 * Should move elements to parallels if it needed and define parallel as roundrobin
	 *
	 * @return void
	 */

	public function testShouldMoveElementsToParallelsIfItNeededAndDefineParallelAsRoundrobin()
	    {
		$container = new Container("anyname_2", 6);

		for ($i = 0; $i < 12; $i++)
		    {
			$container->add("data" . $i, true);
		    }

		$container = new Container("anyname_2_0");
		$this->assertEquals(0, count($container));

		$container = new Container("anyname_2_7");
		$this->assertEquals(0, count($container));

		for ($i = 1; $i <= 6; $i++)
		    {
			$container = new Container("anyname_2_" . $i);
			$this->assertEquals(2, count($container));
		    }

	    } //end testShouldMoveElementsToParallelsIfItNeededAndDefineParallelAsRoundrobin()


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
	 * Should allow make processes always with default sensor
	 *
	 * @return void
	 */

	public function testShouldAllowMakeProcessesAlwaysWithDefaultSensor()
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

	    } //end testShouldAllowMakeProcessesAlwaysWithDefaultSensor()


	/**
	 * Should allow make process as decide the CPU sensor
	 *
	 * @return void
	 */

	public function testShouldAllowMakeProcessAsDecideTheCpuSensor()
	    {
		define("CONTAINER_DIR", __DIR__ . "/container");
		define("CONTAINER_SENSOR", \LoadBalance\Sensors\CPUSensor::class);
		$container = new Container("anyname");

		for ($i = 0; $i < 12; $i++)
		    {
			$container->add("data" . $i);
		    }

		$load    = sys_getloadavg();
		$current = $load[0];

		if ($current <= 15)
		    {
			$expected = 0;
		    }
		else if ($current > 15 && $current <= 30)
		    {
			$expected = 5;
		    }
		else if ($current > 30 && $current <= 50)
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
	    } //end ShouldAllowMakeProcessAsDecideTheCpuSensor()


    } //end class

?>
