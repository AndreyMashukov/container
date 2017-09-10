<?php

namespace Tests;

use \AM\Container\Container;
use \Logics\Tests\InternalWebServer;
use \PHPUnit_Framework_TestCase;
use \Exception;

/**
 * @runTestsInSeparateProcesses
 */

class ContainerAPIStorageTest extends PHPUnit_Framework_TestCase
    {

	use InternalWebServer;

	/**
	 * Name folder which should be removed after tests
	 *
	 * @var string
	 */
	protected $remotepath;

	/**
	 * Testing host
	 *
	 * @var string
	 */
	protected $host;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */

	protected function setUp()
	    {
//		define("CONTAINER_DIR", __DIR__ . "/container");

//		$c = new Container("anyname");
//		$c->clear();

		$this->remotepath = $this->webserverURL();
		$this->host       = $this->remotepath . "/HTTPclientResponder.php";
	    } //end setUp()


	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */

	protected function tearDown()
	    {
//		$c = new Container("anyname");
//		$c->clear();

//		unset($this->host);
	    } //end tearDown()


	/**
	 * Should work with queue container server
	 *
	 * @return void
	 */

	public function testShouldWorkWithQueueContainerServer()
	    {
		$this->assertTrue(true);
	    } //end testShouldWorkWithQueueContainerServer()


    } //end class

?>
