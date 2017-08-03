<?php

namespace Tests;

use \Container\Sensors\DefaultSensor as Sensor;
use \PHPUnit\Framework\TestCase;
use \SimpleXMLElement;

class DefaultSensorTest extends TestCase
    {

	/**
	 * Should alwais return zero
	 *
	 * @return void
	 */

	public function testShouldAlwaysReturnZero()
	    {
		$sensor = new Sensor();
		$this->assertEquals(0, $sensor::calculate());
	    } //end testShouldAlwaysReturnZero()


    } //end class

?>
