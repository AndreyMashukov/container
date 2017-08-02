<?php

namespace Container;

use \Container\Toggles\Toggle;
use \Container\Toggles\DefaultToggle;

class Throttle
    {

	/**
	 * Used toggle
	 *
	 * @var Toggle
	 */
	private $_toggle;

	/**
	 * Prepare Throttle to work
	 *
	 * @param Toggle $toggle Toggle for processing pause
	 *
	 * @return void
	 */

	public function __construct(Toggle $toggle = null)
	    {
		if ($toggle !== null)
		    {
			$this->_toggle = $toggle;
		    }
		else
		    {
			$this->_toggle = new DefaultToggle();
		    } //end if

	    } //end __construct()


	/**
	 * Run, make pause
	 *
	 * @return void
	 */

	public function run()
	    {
		sleep($this->_toggle->calculate());
	    } //end run()


    } //end class


?>
