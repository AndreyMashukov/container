<?php

namespace Tests;

use \Container\Throttle;
use \Container\Toggles\CPUToggle;
use \PHPUnit\Framework\TestCase;
use \SimpleXMLElement;

 /**
  * @runTestsInSeparateProcesses
  */

class AvitoPhoneTest extends TestCase
    {

	/**
	 * Should allow make processes always with default toggle
	 *
	 * @return void
	 */

	public function testShouldAllowMakeProcessesAlwaysWithDefaultToggle()
	    {
		$throttle = new Throttle();
		$time     = time();
		$throttle->run();
		$secondtime = time();
		$this->assertEquals($time, $secondtime);
	    } //end testShouldAllowMakeProcessesAlwaysWithDefaultToggle()


	/**
	 * Should allow make process as decide the CPU toggle
	 *
	 * @return void
	 */

	public function testShouldAllowMakeProcessAsDecideTheCpuToggle()
	    {
		$load   = sys_getloadavg();
		$sum    = 0;
		foreach ($load as $value)
		    {
			$sum += $value;
		    } //end foreach

		$middle = $value/3;

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

		$throttle = new Throttle(new CPUToggle());
		$time     = time();
		$exp      = $time + $expected;
		$throttle->run();
		$secondtime = time();
		$this->assertEquals($exp, $secondtime);
	    } //end ShouldAllowMakeProcessAsDecideTheCpuToggle()


    } //end class

?>
