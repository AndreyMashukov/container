<?php

namespace Tests;

use \Container\Toggles\DefaultToggle as Toggle;
use \PHPUnit\Framework\TestCase;
use \SimpleXMLElement;

class DefaultToggleTest extends TestCase
    {

	/**
	 * Should alwais return zero
	 *
	 * @return void
	 */

	public function testShouldAlwaysReturnZero()
	    {
		$toggle = new Toggle();
		$this->assertEquals(0, $toggle->calculate());
	    } //end testShouldAlwaysReturnZero()


    } //end class

?>
