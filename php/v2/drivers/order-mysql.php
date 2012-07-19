<?php

include_once 'api_producer/v2/drivers/mysql.php';

/*
 * NOTE: Because MySQL can only index upto 1000 bytes (767 characters),
 * the app field is also stored as sha-256. The indexing and unique key
 * are based on this field. Read queries should ensure the field is removed
 * before returning data (it is an internal-to-this-particular-driver-only
 * field).
 */

class NodegroupsAPIV2DriverOrderMySQL extends APIProducerV2DriverMySQL {

	public function __construct($slave_okay, $config = array()) {
		if(!array_key_exists('database', $config)) {
			$config['database'] = 'nodegroups';
		}

		parent::__construct($slave_okay, $config);
	}

	public function __deconstruct() {
		parent::__deconstruct();
	}

	/**
	 * Get nodegroup/app order
	 * @param array app, nodegroup
	 * @return mixed array (which may be empty) of details or false
	 */
	public function getOrder($input) {
		$fields = array(
			'_appsha' => '`_appsha`',
			'app' => '`app`',
			'nodegroup' => '`nodegroup`',
		);
		$outfields = array(
			'`app`',
			'`nodegroup`',
			'`order`',
		);

		if(array_key_exists('nodegroup', $input)) {
			$input['nodegroup'] = stripAt($input['nodegroup']);
		}

		if(array_key_exists('app', $input)) {
			$input['_appsha'] = hash('sha256', $input['app']);
		}

		list($binds, $sets, $values) =
			$this->prepFields($fields, $input);

		return $this->select(array(
			'_binds' => $binds,
			'_one' => true,
			'_values' => $values,
			'from' => '`' . $this->prefix . 'order`',
			'select' => implode(', ', $outfields),
			'where' => implode(' AND ', $sets),
		));
	}

	/**
	 * Search orderings
	 * @param array $search (as built by buildQuery())
	 * @return mixed array (which may be empty) of details or false
	 */
	public function getOrderings($search) {
		// TODO: stripAt

		$outfields = array(
			'`app`',
			'`nodegroup`',
			'`order`',
		);
		$parsed = $this->parseQuery($search);

		$select = array(
			'_binds' => $parsed['binds'],
			'_values' => $parsed['values'],
			'from' => '`' . $this->prefix . 'order`',
			'select' => implode(', ', $outfields),
			'where' => $parsed['where'],
		);

		$count = $this->select(array_merge($select, array(
			'_one' => true,
			'select' => 'COUNT(*) AS `count`',
		)));

		$this->count = $count['count'];

		$select = array_merge($this->applyParameters(), $select);

		return $this->select($select);
	}

	/**
	 * Delete an app nodegroup tuple
	 * @param array $input app, nodegroup
	 * @return bool
	 */
	public function removeOrder($input) {
		$binds = '';
		$fields = array(
			'_appsha' => '`_appsha`',
			'app' => '`app`',
			'nodegroup' => '`nodegroup`',
		);
		$query = 'DELETE FROM `' . $this->prefix . 'order` WHERE ';
		$sets = array();
		$status = false;
		$values = array();

		if(array_key_exists('nodegroup', $input)) {
			$input['nodegroup'] = stripAt($input['nodegroup']);
		}

		if(array_key_exists('app', $input)) {
			$input['_appsha'] = hash('sha256', $input['app']);
		}

		list($binds, $sets, $values) =
			$this->prepFields($fields, $input);

		$query .= implode(' AND ', $sets);
		$query .= ' LIMIT 1';

		$status = $this->queryWrite($query, $binds, $values);
		if($status === 0 || $status === 1) {
			return true;
		}

		if($status > 1) {
			$this->error = 'More than one row deleted';
		}

		return false;
	}

	/**
	 * Delete from order
	 * @param array $input app, nodegroup
	 * @return bool
	 */
	public function removeOrderings($input) {
		$binds = '';
		$fields = array(
			'_appsha' => '`_appsha`',
			'app' => '`app`',
			'nodegroup' => '`nodegroup`',
		);
		$query = 'DELETE FROM `' . $this->prefix . 'order` WHERE ';
		$sets = array();
		$status = false;
		$values = array();

		if(array_key_exists('nodegroup', $input)) {
			$input['nodegroup'] = stripAt($input['nodegroup']);
		}

		if(array_key_exists('app', $input)) {
			$input['_appsha'] = hash('sha256', $input['app']);
		}

		list($binds, $sets, $values) =
			$this->prepFields($fields, $input);

		if(empty($sets)) {
			$this->error = 'Missing where';
			return false;
		}

		$query .= implode(' AND ', $sets);

		$status = $this->queryWrite($query, $binds, $values);
		if($status === 0 || $status === 1) {
			return true;
		}

		if($status > 1) {
			$this->error = 'More than one row deleted';
		}

		return false;
	}

	/**
	 * Set order for a nodegroup
	 * @param array $input
	 * @return mixed array with details or false
	 */
	public function setOrder($input) {
		$binds = '';
		$fields = array(
			'_appsha' => '`_appsha`',
			'app' => '`app`',
			'nodegroup' => '`nodegroup`',
			'order' => '`order`',
		);
		$query = 'INSERT INTO `' . $this->prefix . 'order` SET ';
		$sets = array();
		$status = false;
		$values = array();

		if(array_key_exists('nodegroup', $input)) {
			$input['nodegroup'] = stripAt($input['nodegroup']);
		}

		if(array_key_exists('app', $input)) {
			$input['_appsha'] = hash('sha256', $input['app']);
		}

		list($binds, $sets, $values) =
			$this->prepFields($fields, $input);

		$query .= implode(', ', $sets);
		$query .= ' ON DUPLICATE KEY UPDATE `order` = VALUES(`order`)';

		$status = $this->queryWrite($query, $binds, $values);
		if($status === 0 || $status === 1 || $status === 2) {
			return $this->getOrder(array(
				'_appsha' => $input['_appsha'],
				'nodegroup' => $input['nodegroup'],
			));
		}

		if($status > 2) {
			$this->error = 'More than one row added';
		}

		return false;
	}

	/**
	 * Add order to history
	 * @param array $input
	 * @return bool
	 */
	public function setOrderHistory($input) {
		$binds = '';
		$fields = array(
			'_appsha' => '`_appsha`',
			'action' => '`action`',
			'app' => '`app`',
			'nodegroup' => '`nodegroup`',
			'old_order' => '`old_order`',
			'new_order' => '`new_order`',
			'timestamp' => '`timestamp`',
			'user' => '`user`',
		);
		$query = 'INSERT INTO `' .
			$this->prefix . 'order_history` SET ';
		$sets = array();
		$status = false;
		$values = array();

		if(array_key_exists('nodegroup', $input)) {
			$input['nodegroup'] = stripAt($input['nodegroup']);
		}

		if(array_key_exists('app', $input)) {
			$input['_appsha'] = hash('sha256', $input['app']);
		}

		list($binds, $sets, $values) =
			$this->prepFields($fields, $input);

		$query .= implode(', ', $sets);

		$status = $this->queryWrite($query, $binds, $values);
		if($status == 1) {
			return true;
		}

		if($status > 1) {
			$this->error = 'More than one row added';
		}

		return false;
	}

}

?>
