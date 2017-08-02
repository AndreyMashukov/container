<?php

namespace Container\Toggles;

abstract class Toggle
    {

	/**
	 * Calculate pause to sleep machine
	 *
	 * @retutn int Seconds to sleep
	 */

	abstract public function calculate():int;

    } //end class

?>
