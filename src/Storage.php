<?php

namespace AM\Container;

abstract class Storage
    {

	/**
	 * Order
	 *
	 * @var array Order
	 */
	private $_order;

	/**
	 * Name of container
	 *
	 * @var string Container name
	 */
	private $_name;

	/**
	 * Prepare class to work
	 *
	 * @param string $containername Name of container
	 * @param int    $limit         Order limit
	 *
	 * @return void
	 */

	abstract public function __construct(string $name, int $limit = 0);


	/**
	 * Add element
	 *
	 * @param array  $data   Element
	 * @param string $suffix Container name suffix
	 *
	 * @return void
	 */

	abstract public function addElement(array $data, string $suffix);


	/**
	 * Get container order
	 *
	 * @return array container order
	 */

	abstract public function getOrder():array;


	/**
	 * Get element by position
	 *
	 * @param int $position Element position
	 *
	 * @return array element
	 */

	abstract public function getByPosition(int $position):array;


	/**
	 * Remove element
	 *
	 * @param int $index Element index
	 *
	 * @return bool Remove status
	 */

	abstract public function removeElement(int $index):bool;


	/**
	 * Count elements
	 *
	 * @param string $name Container name
	 *
	 * @return int Count elements
	 */

	abstract public function count(string $name):int;


	/**
	 * Isset
	 *
	 * @param int $position Order position
	 *
	 * @return bool isset status
	 */

	abstract public function isset(int $position):bool;


    } //end class

?>
