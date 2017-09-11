<?php

namespace Tests;

use \AM\Container\Container;
use \Logics\Tests\InternalWebServer;
use \PHPUnit_Framework_TestCase;
use \Exception;
use \Logics\Foundation\HTTP\HTTPclient;
use \DateTime;
use \DateTimeZone;

/**
 * @runTestsInSeparateProcesses
 */

class QueueServerContainerTest extends PHPUnit_Framework_TestCase
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
		$this->remotepath = $this->webserverURL();
		$this->host       = $this->remotepath;

		define("CONTAINER_SALT", "salt");
		define("QUEUE_SERVER", $this->host);
		define("API_KEY", "api_key");
		define("CONTAINER_STORAGE", \AM\Container\QueueServerStorage::class);
	    } //end setUp()


	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */

	protected function tearDown()
	    {
		unset($this->host);
	    } //end tearDown()


	/**
	 * Should add element
	 *
	 * @return void
	 */

	public function testShouldAddElement()
	    {
		$json = [];
		file_put_contents(__DIR__ . "/api/queue/order/get.json", json_encode($json));

		$container = new Container("anyname");
		$json      = [];
		file_put_contents(__DIR__ . "/api/queue/add.json", json_encode(["status" => "ok", "hash" => md5("0")]));
		$this->assertTrue($container->add("first"));
		$json = [];
		file_put_contents(__DIR__ . "/api/queue/add.json", json_encode(["status" => "ok", "hash" => md5("1")]));
		$this->assertTrue($container->add("second"));
		$expected = array("first", "second");

		$json = [md5("0"), md5("1")];
		file_put_contents(__DIR__ . "/api/queue/order/get.json", json_encode($json));

		$datetime = new DateTime("now", new DateTimezone("UTC"));

		$elements   = [];
		$elements[] = array(
			    "creation_time" => $datetime->format("d.m.Y H:i:s"),
			    "data"          => "first",
			    "container"     => "anyname",
			   );

		$elements[] = array(
			    "creation_time" => $datetime->format("d.m.Y H:i:s"),
			    "data"          => "second",
			    "container"     => "anyname",
			   );

		$this->assertEquals(2, count($container));
		$i = 0;
		$json = ["status" => "ok", "data" => $elements[$i]];
		file_put_contents(__DIR__ . "/api/queue/element/get.json", json_encode($json));

		foreach ($container as $key => $element)
		    {
			$now = new DateTime("now", new DateTimezone("UTC"));
			$this->assertEquals($expected[$i], $element["data"]);
			$this->assertEquals($now->format("d-m-Y H:i"), $element["creation_time"]->format("d-m-Y H:i"));
			$this->assertEquals(32, strlen($element["id"]));
			$this->assertEquals($i, $key);
			$container->remove($key);
			$i++;

			if (isset($elements[$i]) === true)
			    {
				$json = ["status" => "ok", "data" => $elements[$i]];
				file_put_contents(__DIR__ . "/api/queue/element/get.json", json_encode($json));
			    } //end if

		    } //end foreach

		$this->assertEquals(0, count($container));
	    } //end testShouldAddElement()


	/**
	 * Should allow to add order count limit
	 *
	 * @return void
	 */

	public function testShouldAllowToAddOrderCountLimit()
	    {
		$json      = [];
		$container = new Container("anyname", 1, 10);
		$elements  = [];
		for ($i = 0; $i < 20; $i++)
		    {
			$json[]     = md5($i);
			$datetime   = new DateTime("now", new DateTimezone("UTC"));
			$elements[] = array(
			    "creation_time" => $datetime->format("d.m.Y H:i:s"),
			    "data"          => "first",
			    "container"     => "anyname",
			);
			$this->assertTrue($container->add("first"));
		    } //end for

		file_put_contents(__DIR__ . "/api/queue/order/get.json", json_encode($json));

		$this->assertEquals(10, count($container));
		$i = 0;
		$json = ["status" => "ok", "data" => $elements[$i]];
		file_put_contents(__DIR__ . "/api/queue/element/get.json", json_encode($json));
		foreach ($container as $key => $element)
		    {
			$this->assertEquals(32, strlen($element["id"]));
			$i++;

			if (isset($elements[$i]) === true)
			    {
				$json = ["status" => "ok", "data" => $elements[$i]];
				file_put_contents(__DIR__ . "/api/queue/element/get.json", json_encode($json));
			    } //end if
		    }

		$this->assertEquals(10, $i);
	    } //end testShouldAddElement()


    } //end class

?>
