<?php

namespace Tests;

use \Container\Throttler;
use \Container\Sensors\CPUSensor;
use \PHPUnit\Framework\TestCase;
use \SimpleXMLElement;

 /**
  * @runTestsInSeparateProcesses
  */

class ThrottlerTest extends TestCase
    {

	/**
	 * Should allow make processes always with default sensor
	 *
	 * @return void
	 */

	public function testShouldAllowMakeProcessesAlwaysWithDefaultSensor()
	    {
		$throttler = new Throttler();
		$time      = time();
		$throttler->run();
		$secondtime = time();
		$this->assertEquals($time, $secondtime);
	    } //end testShouldAllowMakeProcessesAlwaysWithDefaultSensor()


	/**
	 * Should allow make process as decide the CPU sensor
	 *
	 * @return void
	 */

	public function testShouldAllowMakeProcessAsDecideTheCpuSensor()
	    {
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

		$throttler = new Throttler(new CPUSensor());
		$time      = time();
		$exp       = $time + $expected;
		$throttler->run();
		$secondtime = time();
		$this->assertEquals($exp, $secondtime);
	    } //end ShouldAllowMakeProcessAsDecideTheCpuSensor()


    } //end class

?>
