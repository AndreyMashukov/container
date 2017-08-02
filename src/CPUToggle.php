<?php

namespace Container\Toggles;

class CPUToggle extends Toggle
    {

	/**
	 * Calculate pause to sleep machine
	 *
	 * @retutn int Seconds to sleep
	 */

	public function calculate():int
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
			$sleeptime = 0;
		    }
		else if ($middle > 15 && $middle <= 30)
		    {
			$sleeptime = 5;
		    }
		else if ($middle > 30 && $middle <= 50)
		    {
			$sleeptime = 10;
		    }
		else
		    {
			$sleeptime = 60;
		    } //end if

		return $sleeptime;
	    } //end calculate()


    } //end class

?>