<?php

namespace AM\Container;

use \DateTime;
use \DateTimezone;
use \DirectoryIterator;
use \SQLite3;

class SQLiteStorage extends Storage
    {

	/**
	 * Database
	 *
	 * @var SQLdatabase
	 */
	private $_db;

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

		$this->_db = new SQLite3($this->_storage . "/container.db", (SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE));

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
	 * @return bool Status
	 */

	public function addElement(array $element, string $suffix):bool
	    {
		$element["id"] = sha1(uniqid());

		$time = new DateTime("now", new DateTimezone("UTC"));
		$this->_createTables($this->_name . $suffix);

		if ($this->_isset($this->_name . $suffix, $element["id"]) === false)
		    {
			$this->_db->exec(
			    "INSERT INTO " . $this->_name . $suffix . " (" .
			    "contents," .
			    "creationtime," .
			    "message_id) VALUES (" .
			    "'" . $this->_db->escapeString(json_encode($element)) . "'," .
			    "'" . $this->_formatDateTime($time) . "'," .
			    "'" . $this->_db->escapeString($element["id"]) . "')"
			);
		    }

		if ($this->_limit > 0 && count($this->_order) < $this->_limit || $this->_limit === 0)
		    {
			$this->_order[] = $element["id"];
		    } //end if

		return true;
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
		$this->_db->exec(
		    "DELETE FROM " . $this->_name . " " .
		    "WHERE message_id = '" . $this->_db->escapeString($id) . "'"
		);

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
		$id     = $this->_order[$position];
		$result = $this->_db->query(
		    "SELECT " .
		    "contents, " .
		    "creationtime " .
		    "FROM " . $this->_name . " " .
		    "WHERE message_id = '" . $id . "'");

		$row = $result->fetchArray(SQLITE3_ASSOC);

		return json_decode($row["contents"], true);
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
	 * Count elements
	 *
	 * @param string $name Container name
	 *
	 * @return int Count elements
	 */

	public function count(string $name = ""):int
	    {
		if ($this->_limit === 0)
		    {
			if ($name === "")
			    {
				$result = $this->_db->query(
				    "SELECT COUNT(message_id) as count " .
				    "FROM " . $this->_name
				);
			    }
			else
			    {
				$result = $this->_db->query(
				    "SELECT COUNT(message_id) as count " .
				    "FROM " . $name
				);
			    }

			$row = $result->fetchArray(SQLITE3_ASSOC);
			return $row["count"];
		    }
		else
		    {
			return count(array_slice($this->_order, 0, $this->_limit));
		    } //end if

	    } //end count()


	/**
	 * Format DateTime to MySQL format
	 *
	 * @param DateTime $time Time
	 *
	 * @return string Formatted time
	 */

	private function _formatDateTime(DateTime $time = null)
	    {
		if ($time !== null)
		    {
			return $time->format("Y-m-d H:i:s");
		    }
		else
		    {
			return "0000-00-00 00:00:00";
		    }
	    } //end _formatDateTime()


	/**
	 * Check element exist
	 *
	 * @param string $containername Name of container to write
	 * @param string $messageid     Message ID
	 *
	 * @return bool Element exist
	 */

	private function _isset($containername, $messageid)
	    {
		$result = $this->_db->query(
		    "SELECT COUNT(message_id) as count FROM " . $containername . " " .
		    "WHERE message_id = '" . $this->_db->escapeString($messageid) . "'");

		$row = $result->fetchArray(SQLITE3_ASSOC);

		if ((int) $row["count"] !== 0)
		    {
			return true;
		    }
		else
		    {
			return false;
		    }
	    } //end _isset()


	/**
	 * Getting order array
	 *
	 * @return array Order
	 */

	private function _refreshOrder()
	    {
		$this->_createTables($this->_name);

		$order = array();

		if ($this->_limit > 0)
		    {
			$result = $this->_db->query(
			    "SELECT message_id FROM " . $this->_name . " " .
			    "ORDER BY creationtime ASC LIMIT " . $this->_limit
			);
		    }
		else
		    {
			$result = $this->_db->query(
			    "SELECT message_id FROM " . $this->_name
			);
		    }


		while ($row = $result->fetchArray(SQLITE3_ASSOC))
		    {
			if ($this->_limit > 0 && count($order) < $this->_limit || $this->_limit === 0)
			    {
				$order[] = $row["message_id"];
			    } //end if

		    }

		$this->_order = $order;
	    } //end _refreshOrder()


	/**
	 * Create database tables
	 *
	 * @param string $containername Name of container
	 *
	 * @return void
	 */

	private function _createTables($containername)
	    {
		$this->_db->exec(
		    "CREATE TABLE IF NOT EXISTS " . $containername . " (" .
		    "id integer PRIMARY KEY ASC, " .
		    "message_id character(45) NOT NULL, " .
		    "contents blob NOT NULL, " .
		    "creationtime DateTime)"
		);

		$this->_db->exec(
		    "CREATE UNIQUE INDEX IF NOT EXISTS messageidindex_" . $containername . " ON " . $containername . " (message_id)"
		);
	    } //end _createTables()


    } //end class

?>
