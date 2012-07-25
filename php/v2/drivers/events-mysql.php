<?php

include_once 'api_producer/v2/drivers/mysql.php';

class NodegroupsAPIV2DriverEventsMySQL extends APIProducerV2DriverMySQL {

	protected $order = array(
		'ADD' => 1,
		'CREATE' => 0,
		'DELETE' => 3,
		'REMOVE' => 2,
	);

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
	 * Add an event
	 * @param array $input
	 * @return bool
	 */
	public function addEvent($input) {
		if(array_key_exists('nodegroup', $input)) {
			$input['nodegroup'] = stripAt($input['nodegroup']);
		}

		if(array_key_exists('event', $input)) {
			if(array_key_exists($input['event'], $this->order)) {
				$input['_order'] =
					$this->order[$input['event']];
			}
		}

		if(array_key_exists('nodes', $input)) {
			if(count($input['nodes']) < 1) {
				return true;
			}

			$nodes = $input['nodes'];
			unset($input['nodes']);

			return $this->addEventMulti($nodes, $input);
		} else {
			return $this->addEventSingle($input);
		}
	}

	/**
	 * Add a single event (<=1 nodes)
	 * @param array $input
	 * @return bool
	 */
	protected function addEventSingle($input) {
		$binds = '';
		$fields = array(
			'_order' => '`_order`',
			'event' => '`event`',
			'id' => '`id`',
			'node' => '`node`',
			'nodegroup' => '`nodegroup`',
			'timestamp' => '`timestamp`',
			'user' => '`user`',
		);
		$query = 'INSERT INTO `' . $this->prefix . 'events` SET ';
		$sets = array();
		$status = false;
		$values = array();

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

	/**
	 * Add an event (>1 nodes)
	 * @param array $nodes
	 * @param array $input
	 * @return bool
	 */
	public function addEventMulti($nodes, $input) {
		$binds = '';
		$fields = array(
			'_order' => '`_order`',
			'event' => '`event`',
			'id' => '`id`',
			'nodegroup' => '`nodegroup`',
			'timestamp' => '`timestamp`',
			'user' => '`user`',
		);
		$query = 'INSERT INTO `' . $this->prefix . 'events` ';
		$sets = array();
		$status = false;
		$values = array();

		list($t_binds, $t_cols, $t_sets, $t_values) =
			$this->prepFieldsMulti($fields, $input);

		$t_binds .= 's';
		$t_cols[] = '`node`';
		$t_sets[] = '?';

		$query .= '(' . implode(', ', $t_cols) . ') VALUES ';

		$t_sets = '(' . implode(', ', $t_sets) . ')';

		while(list($junk, $node) = each($nodes)) {
			$binds .= $t_binds;
			$sets[] = $t_sets;
			$values = array_merge($values, $t_values, array($node));
		}
		reset($nodes);

		$query .= implode(', ', $sets);

		$status = $this->queryWrite($query, $binds, $values);
		if($status >= 1) {
			return true;
		}

		return false;
	}

	/**
	 * Search events
	 * @param array $search (as built by buildQuery())
	 * @return mixed array (which may be empty) of details or false
	 */
	public function getEvents($search) {
		// TODO: stripAt

		$output_fields = array(
			'`event`',
			'`id`',
			'`node`',
			'`nodegroup`',
			'`timestamp`',
			'`user`',
		);

		$parsed = $this->parseQuery($search);
		$select = array(
			'_binds' => $parsed['binds'],
			'_values' => $parsed['values'],
			'from' => '`' . $this->prefix . 'events`',
			'select' => implode(', ', $output_fields),
			'where' => $parsed['where'],
		);

		$count = $this->select(array_merge($select, array(
			'_one' => true,
			'select' => 'COUNT(*) AS `count`',
		)));

		$this->count = $count['count'];

		$select = array_merge($this->applyParameters(), $select);

		if($this->getParameter('sortField') === 'timestamp') {
			$select['order'] .= ', `_order`';

			if(!is_null($this->getParameter('sortDir'))) {
				$select['order'] .= ' ' .
					$this->getParameter('sortDir');
			}
		}

		return $this->select($select);
	}
}

?>

