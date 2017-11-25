<?php

namespace AM\Container;

use \DirectoryIterator;

class FilesStorage extends Storage
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
		$this->_order   = [];
		$this->_limit   = $limit;
		$this->_name    = $name;
		$this->_storage = "/home/container/";

		if (defined("CONTAINER_DIR") === true)
		    {
			$this->_storage = CONTAINER_DIR;
		    } //end if

		$dirs = [
		    $this->_storage,
		    $this->_storage . "/recover_data",
		    $this->_storage . "/recover_data/" . $name,
		];

		foreach ($dirs as $dir)
		    {
			if (file_exists($dir) === false)
			    {
				mkdir($dir);
			    } //end if

		    } //end foreach


		if (file_exists($this->_storage . "/" . $name) === true)
		    {
			$this->_refreshOrder();
		    } //end if

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
	 * @return bool Status
	 */

	public function addElement(array $element, string $suffix):bool
	    {
		$element["id"] = sha1(uniqid());

		if (file_exists($this->_storage . "/" . $this->_name . $suffix) === false)
		    {
			mkdir($this->_storage . "/" . $this->_name . $suffix);
		    } //end if

		if ($this->_limit > 0)
		    {
			if (count($this->_order) < $this->_limit)
			    {
				$this->_order[] = $element["id"];
			    } //end if

		    }
		else
		    {
			$this->_order[] = $element["id"];
		    } //end if

		do
		    {
			file_put_contents($this->_storage . "/" . $this->_name . $suffix . "/" . $element["id"], gzencode(serialize($element)));
			$infile = unserialize(gzdecode(file_get_contents($this->_storage . "/" . $this->_name . $suffix .  "/" . $element["id"])));

			$this->_makeRecoverData(serialize($infile), $element["id"], $suffix);
		    } while ($infile !== $element);

		return true;
	    } //end addElement()


	/**
	 * Make recover data file
	 *
	 * @param string $content Content to file
	 * @param string $id      File id
	 * @param string $suffix  Suffix to filename
	 *
	 * @return void
	 */

	private function _makeRecoverData(string $content, string $id, string $suffix)
	    {
		while (true)
		    {
			file_put_contents($this->_storage . "/recover_data/" . $this->_name . $suffix . "/" . $id, $content);
			$infile = file_get_contents($this->_storage . "/recover_data/" . $this->_name . $suffix . "/" . $id);
			if ($infile === $content)
			    {
				break;
			    } //end if

		    } //end while

	    } //end _makeRecoverData()


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
		unlink($this->_storage . "/" . $this->_name . "/" . $id);
		unlink($this->_storage . "/recover_data/" . $this->_name . "/" . $id);

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
		$id      = $this->_order[$position];
		@$result = unserialize(gzdecode(file_get_contents($this->_storage . "/" . $this->_name . "/" . $id)));

		if ($result === false)
		    {
			$recovered = unserialize(file_get_contents($this->_storage . "/recover_data/" . $this->_name . "/" . $id));
			unlink($this->_storage . "/" . $this->_name . "/" . $id);

			return (($recovered === false) ? [] : $recovered);
		    } //end if

		return $result;
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

		foreach (new DirectoryIterator($this->_storage . "/" . $this->_name) as $fileInfo)
		    {
			if($fileInfo->isDot() === false && (int) $fileInfo->getSize() !== 0 && $fileInfo->isDir() === false)
			    {
				$this->_order[] = $fileInfo->getFilename();
				$count++;
				if ($this->_limit > 0 && $count >= $this->_limit)
				    {
					break;
				    } //end if

			    }
			else if ((int) $fileInfo->getSize() === 0)
			    {
				unlink($this->_storage . "/" . $this->_name . "/" . $fileInfo->getFilename());
			    } //end if

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
			if (file_exists($this->_storage . "/" . $name) === true)
			    {
				foreach (new DirectoryIterator($this->_storage . "/" . $name) as $fileInfo)
				    {
					if($fileInfo->isDot() === false && (int) $fileInfo->getSize() !== 0 && $fileInfo->isDir() === false)
					    {
						$count++;
					    } //end if

				    } //end foreach

			    } //end if

		    }
		else
		    {
			$count = count($this->_order);
		    } //end if

		return $count;
	    } //end count()


    } //end class

?>
