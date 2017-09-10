<?php

namespace AM\Container;

use \AM\Container\FilesStorage;
use \Countable;
use \LoadBalance\Sensors\Sensor;
use \LoadBalance\Throttler;
use \DateTime;
use \DateTimezone;
use \Iterator;

class Container implements Iterator, Countable
    {

	/**
	 * Container name
	 *
	 * @var string Name of container
	 */
	private $_name;

	/**
	 * Storage
	 *
	 * @var Storage data storage
	 */
	private $_storage;

	/**
	 * Parallels
	 *
	 * @var int Parallels
	 */
	private $_parallels;

	/**
	 * Position for iterator
	 *
	 * @var int Position
	 */
	private $_position;

	/**
	 * Throttler
	 *
	 * @var Throttler
	 */
	private $_throttler;

	/**
	 * Last parallel
	 *
	 * @var int
	 */
	private $_parallel = 1;

	/**
	 * Prepare container to work
	 *
	 * @param string $name      Name of current container
	 * @param int    $parallels Count of parallels
	 * @param int    $limit     Order limit
	 *
	 * @return void
	 */

	public function __construct(string $name, int $parallels = 1, int $limit = 0)
	    {
		$this->_name = $name;

		if ($limit > 0)
		    {
			$this->_limit = $limit;
		    } //end if

		$this->_throttler = new Throttler();

		if (defined("CONTAINER_SENSOR") === true)
		    {
			$constantsensor = CONTAINER_SENSOR;
			$sensor         = new $constantsensor();
			if ($sensor instanceof Sensor)
			    {
				$this->_throttler = new Throttler($sensor);
			    } //end if

		    } //end if

		$this->_parallels = $parallels;

		if (defined("CONTAINER_STORAGE") === false)
		    {
			$this->_storage = new FilesStorage($name, $limit);
		    } //end if

	    } //end __construct()


	/**
	 * Add element
	 *
	 * @param mixed $data       Any data to container
	 * @param bool  $roundrobin If not need check count in adding elements
	 *
	 * @return bool Operation result
	 */

	public function add($data, bool $roundrobin = false):bool
	    {
		if ($this->_parallels === 1)
		    {
			$this->_addToStorage($data);
		    }
		else
		    {
			if ($roundrobin === false)
			    {
				$parallel = $this->_getParallel();
			    }
			else
			    {
				$parallel = $this->_parallel;
				if ($parallel < $this->_parallels)
				    {
					$this->_parallel++;
				    }
				else
				    {
					$this->_parallel = 1;
				    } //end if

				$parallel = "_" . $parallel;

			    } //end if

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
		return $this->_storage->count($name);
	    } //end _count()


	/**
	 * Clear container
	 *
	 * @return void
	 */

	public function clear()
	    {
		$order = $this->_storage->getOrder();
		foreach ($order as $key => $id)
		    {
			$this->_storage->removeElement($key);
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
		$datetime = new DateTime("now", new DateTimezone("UTC"));

		$element = array(
			    "creation_time" => $datetime->format("d.m.Y H:i:s"),
			    "data"          => $data,
			    "container"     => $this->_name . $parallel,
			   );

		$this->_storage->addElement($element, $parallel);
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
	 * @param int $index Element index
	 *
	 * @return void
	 */

	public function remove(int $index)
	    {
		$this->_storage->removeElement($index);
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
		$this->_throttler->run();
		return $this->_convertTime($this->_storage->getByPosition($this->_position));
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
		return $this->_storage->isset($this->_position);
	    } //end valid()


	/**
	 * Count
	 *
	 * @retutn int count
	 */

	public function count():int
	    {
		return $this->_storage->count();
	    } //end count()


    } //end class

?>