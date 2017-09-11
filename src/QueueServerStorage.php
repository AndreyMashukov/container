<?php

namespace AM\Container;

use \Logics\Foundation\HTTP\HTTPclient;

class QueueServerStorage extends Storage
    {

	/**
	 * Order
	 *
	 * @var array Order
	 */
	private $_order;

	/**
	 * Storage
	 *
	 * @var string Storage path
	 */
	private $_storage;

	/**
	 * Name of container
	 *
	 * @var string Container name
	 */
	private $_name;

	/**
	 * Order limit
	 *
	 * @var int Limit
	 */
	private $_limit = 0;

	/**
	 * Prepare class to work
	 *
	 * @param string $containername Name of container
	 * @param int    $limit         Order limit
	 *
	 * @return void
	 */

	public function __construct(string $name, int $limit = 0)
	    {
		$this->_limit   = $limit;
		$this->_name    = $name;
		$this->_storage = QUEUE_SERVER;
		$this->_refreshOrder();
	    } //end __construct()


	/**
	 * Get container order
	 *
	 * @return array container order
	 */

	public function getOrder():array
	    {
		return $this->_order;
	    } //end getOrder()


	/**
	 * Add element
	 *
	 * @param array  $element Element
	 * @param string $suffix Container name suffix
	 *
	 * @return bool status
	 */

	public function addElement(array $element, string $suffix):bool
	    {
		$http  = new HTTPclient($this->_storage . "/api/queue/add.json", [
		    "key"            => API_KEY,
		    "container_name" => $this->_name . $suffix . "_" . CONTAINER_SALT,
		    "data"           => json_encode($element),
		]);

		$response = json_decode($http->post(), true);
		if ($response["status"] === "ok")
		    {
			if ($this->_limit === 0)
			    {
				$this->_order[] = $response["hash"];
			    }
			else if (count($this->_order) < $this->_limit)
			    {
				$this->_order[] = $response["hash"];
			    } //end if

			return true;
		    } //end if

		return false;
	    } //end addElement()


	/**
	 * Remove element
	 *
	 * @param int $index Element index
	 *
	 * @return bool Remove status
	 */

	public function removeElement(int $index):bool
	    {
		$id = $this->_order[$index];
		unset($this->_order[$index]);

		$http  = new HTTPclient($this->_storage . "/api/queue/del.json", [
		    "key"  => API_KEY,
		    "hash" => $id,
		]);
		$http->post();

		return true;
	    } //end removeElement()


	/**
	 * Get element by position
	 *
	 * @param int $position Element position
	 *
	 * @return array element
	 */

	public function getByPosition(int $position):array
	    {
		$id = $this->_order[$position];
		$http  = new HTTPclient($this->_storage . "/api/queue/element/get.json", [
		    "key"  => API_KEY,
		    "hash" => $id,
		]);
		$response = json_decode($http->post(), true);

		if ($response["status"] === "ok")
		    {
			$data       = json_decode($response["data"], true);
			$data["id"] = $id;

			return $data;
		    } //end if

	    } //end getByPosition()


	/**
	 * Isset
	 *
	 * @param int $position Order position
	 *
	 * @return bool isset status
	 */

	public function isset(int $position):bool
	    {
		return isset($this->_order[$position]);
	    } //end isset()


	/**
	 * Refresh order
	 *
	 * @return void
	 */

	private function _refreshOrder()
	    {
		$this->_order = [];

		$count = 0;

		$http  = new HTTPclient($this->_storage . "/api/queue/order/get.json", ["key" => API_KEY, "container_name" => $this->_name . "_" . CONTAINER_SALT]);
		$order = json_decode($http->post(), true);

		if ($this->_limit > 0)
		    {
			$order = array_slice($order, 0, $this->_limit);
		    } //end if

		foreach ($order as $item)
		    {
			$this->_order[] = $item["hash"];
		    } //end foreach

	    } //end _refreshOrder()


	/**
	 * Count elements
	 *
	 * @param string $name Container name
	 *
	 * @return int Count elements
	 */

	public function count(string $name = ""):int
	    {
		$count = 0;

		if ($name !== "")
		    {
			$http  = new HTTPclient($this->_storage . "/api/queue/order/get.json", ["key" => API_KEY, "container_name" => $name . "_" . CONTAINER_SALT]);
			$order = json_decode($http->post(), true);

			$count = count($order);
		    }
		else
		    {
			$count = count($this->_order);
		    } //end if

		return $count;
	    } //end count()


    } //end class

?>
