<?php

namespace AdService;

use \Countable;
use \Container\Toggles\Toggle;
use \Container\Throttle;
use \DateTime;
use \DateTimezone;
use \DirectoryIterator;
use \DOMDocument;
use \Iterator;

class Container implements Iterator, Countable
    {

	/**
	 * Name of container
	 *
	 * @var string Container name
	 */
	private $_name;

	/**
	 * Storage
	 *
	 * @var string Storage path
	 */
	private $_storage;

	/**
	 * Parallels
	 *
	 * @var int Parallels
	 */
	private $_parallels;

	/**
	 * Order
	 *
	 * @var array Order
	 */
	private $_order = [];

	/**
	 * Position for iterator
	 *
	 * @var int Position
	 */
	private $_position;

	/**
	 * Throttle
	 *
	 * @var Throttle
	 */
	private $_throttle;


	/**
	 * Prepare container to work
	 *
	 * @param string $name Name of current container
	 *
	 * @return void
	 */

	public function __construct(string $name, $parallels = 1)
	    {
		$this->_throttle = new Throttle();

		if (defined("TOGGLE") === true)
		    {
			if (TOGGLE instanceof Toggle)
			    {
				$this->_throttle = new Throttle(TOGGLE);
			    } //end if

		    } //end if


		$this->_parallels = $parallels;

		$this->_name    = $name;
		$this->_storage = "/home/container/";

		if (defined("CONTAINER_DIR") === true)
		    {
			$this->_storage = CONTAINER_DIR;
		    } //end if

		if (file_exists($this->_storage . "/" . $this->_name) === false)
		    {
			mkdir($this->_storage . "/" . $this->_name);
		    } //end if

		foreach (new DirectoryIterator($this->_storage . "/" . $this->_name) as $fileInfo)
		    {
			if($fileInfo->isDot() === false && (int) $fileInfo->getSize() !== 0 && $fileInfo->isDir() === false)
			    {
				$this->_order[] = $fileInfo->getFilename();
			    }
			else if ((int) $fileInfo->getSize() === 0)
			    {
				unlink($this->_storage . "/" . $this->_name . "/" . $fileInfo->getFilename());
			    } //end if

		    } //end foreach

	    } //end __construct()


	/**
	 * Add element
	 *
	 * @param mixed $data Any data to container
	 *
	 * @return bool Operation result
	 */

	public function add($data):bool
	    {
		if ($this->_parallels === 1)
		    {
			$this->_addToStorage($data);
		    }
		else
		    {
			$parallel = $this->_getParallel();
			$this->_addToStorage($data, $parallel);
		    } //end if

		return true;
	    } //end add()


	/**
	 * Get parallel
	 *
	 * @return string Suffix of parallel
	 */

	private function _getParallel():string
	    {
		$counts = [];
		for ($i = $this->_parallels; $i > 0; $i--)
		    {
			$name = $this->_name . "_" . $i;

			$counts[$this->_count($name)] = $i;
		    } //end for

		ksort($counts);

		return "_" . array_shift($counts);
	    } //end _getParallel()


	/**
	 * Get order by name of container
	 *
	 * @param string $name Container name
	 *
	 * @return int Count of elements
	 */

	private function _count(string $name):int
	    {
		$count = 0;
		if (file_exists($this->_storage . "/" . $name) === false)
		    {
			mkdir($this->_storage . "/" . $name);
		    } //end if

		foreach (new DirectoryIterator($this->_storage . "/" . $name) as $fileInfo)
		    {
			if($fileInfo->isDot() === false && (int) $fileInfo->getSize() !== 0 && $fileInfo->isDir() === false)
			    {
				$count++;
			    } //end if

		    } //end foreach

		return $count;
	    } //end _count()


	/**
	 * Clear container
	 *
	 * @return void
	 */

	public function clear()
	    {
		foreach ($this->_order as $key => $id)
		    {
			$this->remove($key);
		    } //end foreach

	    } //end clear()


	/**
	 * Add element to storage
	 *
	 * @param mixed $data Data to save
	 *
	 * @return void
	 */

	private function _addToStorage($data, string $parallel = "")
	    {
		$id             = sha1(uniqid());
		$this->_order[] = $id;

		$datetime = new DateTime("now", new DateTimezone("UTC"));

		$element = array(
			    "id"            => $id,
			    "creation_time" => $datetime->format("d.m.Y H:i:s"),
			    "data"          => $data,
			    "container"     => $this->_name . $parallel,
			   );

		do
		    {
			file_put_contents($this->_storage . "/" . $this->_name . $parallel . "/" . $id, gzencode(serialize($element)));
			$infile = unserialize(gzdecode(file_get_contents($this->_storage . "/" . $this->_name . $parallel .  "/" . $id)));
		    } while ($infile !== $element);

	    } //end addToStorage()


	/**
	* Convert time
	*
	* @param array $element Element
	*
	* @return array Converted data
	*/

	private function _convertTime(array $element)
	    {
		return array(
			    "id"            => $element["id"],
			    "creation_time" => new DateTime($element["creation_time"], new DateTimezone("UTC")),
			    "data"          => $element["data"],
			    "container"     => $element["container"],
			   );

	    } //end _convertTime()


	/**
	 * Remove element
	 *
	 * @param $index Element id
	 *
	 * @return void
	 */

	public function remove($index)
	    {
		$id = $this->_order[$index];
		unset($this->_order[$index]);
		unlink($this->_storage . "/" . $this->_name . "/" . $id);
	    } //end remove()


	/**
	 * Rewind
	 *
	 * @return void
	 */

	public function rewind()
	    {
		$this->_position = 0;
	    } //end rewind()


	/**
	 * Current element
	 *
	 * @return array Element
	 */

	public function current():array
	    {
		$this->_throttle->run();
		$id = $this->_order[$this->_position];
		return $this->_convertTime(unserialize(gzdecode(file_get_contents($this->_storage . "/" . $this->_name . "/" . $id))));
	    } //end current()


	/**
	 * Key
	 *
	 * @return int Position index
	 */

	public function key():int
	    {
		return $this->_position;
	    } //end key()


	/**
	 * Next iteration
	 *
	 * @return void
	 */

	public function next()
	    {
		++$this->_position;
	    } //end next()


	/**
	 * Validate element
	 *
	 * @return bool Exist element
	 */

	public function valid():bool
	    {
		return isset($this->_order[$this->_position]);
	    } //end valid()


	/**
	 * Count
	 *
	 * @retutn int count
	 */

	public function count():int
	    {
		return count($this->_order);
	    } //end count()


    } //end class

?>