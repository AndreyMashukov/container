<?php

namespace Tests;

if (isset($_SERVER["HTTP_USER_AGENT"]) === true)
    {

	$proxies = [
	    "111.111.111.111:11",
	    "222.222.222.222:22",
	    "333.333.333.333:33",
	    "444.444.444.444:44",
	];

	echo implode("\n", $proxies);

    } //end if


?>
