<?php

include_once 'api_producer/v2/drivers/mysql.php';

class NodegroupsAPIV2DriverNodegroupsMySQL extends APIProducerV2DriverMySQL {

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
	 * Add a nodegroup
	 * @param array $input
	 * @return mixed array with details or false
	 */
	public function addNodegroup($input) {
		$binds = '';
		$fields = array(
			'description' => '`description`',
			'expression' => '`expression`',
			'nodegroup' => '`nodegroup`',
		);
		$query = 'INSERT INTO `' . $this->prefix . 'nodegroups` SET ';
		$sets = array();
		$status = false;
		$values = array();

		if(array_key_exists('nodegroup', $input)) {
			$input['nodegroup'] = stripAt($input['nodegroup']);
		}

		list($binds, $sets, $values) =
			$this->prepFields($fields, $input);

		$query .= implode(', ', $sets);

		$status = $this->queryWrite($query, $binds, $values);
		if($status == 1) {
			return $this->getNodegroupByID($input['nodegroup']);
		}

		if($status > 1) {
			$this->error = 'More than one row added';
		}

		return false;
	}

	/**
	 * Add a nodegroup to history
	 * @param array $input
	 * @return bool
	 */
	public function addNodegroupHistory($input) {
		$binds = '';
		$fields = array(
			'action' => '`action`',
			'description' => '`description`',
			'expression' => '`expression`',
			'nodegroup' => '`nodegroup`',
			'timestamp' => '`timestamp`',
			'user' => '`user`',
		);
		$query = 'INSERT INTO `' .
			$this->prefix . 'nodegroup_history` SET ';
		$sets = array();
		$status = false;
		$values = array();

		if(array_key_exists('nodegroup', $input)) {
			$input['nodegroup'] = stripAt($input['nodegroup']);
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

	/**
	 * Delete a nodegroup
	 * @param string $nodegroup
	 * @return bool
	 */
	public function deleteNodegroup($nodegroup) {
		$binds = 's';
		$query = 'DELETE FROM `' . $this->prefix . 'nodegroups`' .
			' WHERE `nodegroup` = ? LIMIT 1';
		$status = false;
		$values = array();

		$nodegroup = stripAt($nodegroup);

		$status = $this->queryWrite($query, 's', array($nodegroup));
		if($status === 0 || $status === 1) {
			return true;
		}

		if($status > 1) {
			$this->error = 'More than one row deleted';
		}

		return false;
	}

	/**
	 * Get the list of children for given nodegroup
	 * @param string $nodegroup
	 * @return array children, inherited
	 */
	public function getChildren($nodegroup) {
		$inherited = array();
		$children = array();

		$nodegroup = stripAt($nodegroup);

		$data = $this->select(array(
			'_binds' => 's',
			'_values' => array($nodegroup),
			'from' => '`' . $this->prefix . 'children`',
			'where' => '`nodegroup` = ?',
		));

		while(list($junk, $datum) = each($data)) {
			$children[] = $datum['child'];

			if($datum['inherited']) {
				$inherited[] = $datum['child'];
			}
		}

		return array(
			'inherited' => $inherited,
			'nodegroups' => $children,
		);
	}

	/**
	 * Get a nodegroup by name
	 * @param string $nodegroup
	 * @return mixed array (which may be empty) of details or false
	 */
	public function getNodegroupByID($input) {
		$input = stripAt($input);

		return $this->select(array(
			'_binds' => 's',
			'_one' => true,
			'_values' => array($input),
			'from' => '`' . $this->prefix . 'nodegroups`',
			'where' => '`nodegroup` = ?',
		));
	}

	/**
	 * Search nodegroups
	 * @param array $search (as built by buildQuery())
	 * @return mixed array (which may be empty) of details or false
	 */
	public function getNodegroups($search) {
		// TODO: stripAt

		$output_fields = array(
			'`description`',
			'`expression`',
			'`nodegroup`',
		);

		$parsed = $this->parseQuery($search);
		$select = array(
			'_binds' => $parsed['binds'],
			'_values' => $parsed['values'],
			'from' => '`' . $this->prefix . 'nodegroups`',
			'select' => implode(', ', $output_fields),
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
	 * Get the list of parents for given nodegroup
	 * @param string $nodegroup
	 * @return array children, inherited
	 */
	public function getParents($nodegroup) {
		$inherited = array();
		$parents = array();

		$nodegroup = stripAt($nodegroup);

		$data = $this->select(array(
			'_binds' => 's',
			'_values' => array($nodegroup),
			'from' => '`' . $this->prefix . 'children`',
			'where' => '`child` = ?',
		));

		while(list($junk, $datum) = each($data)) {
			$parents[] = $datum['nodegroup'];

			if($datum['inherited']) {
				$inherited[] = $datum['nodegroup'];
			}
		}

		return array(
			'inherited' => $inherited,
			'nodegroups' => $parents,
		);
	}

	/**
	 * Modify a nodegroup
	 * @param array $input
	 * @return mixed array with details or false
	 */
	public function modifyNodegroup($input) {
		$binds = '';
		$fields = array(
			'description' => '`description`',
			'expression' => '`expression`',
		);
		$query = 'UPDATE `' . $this->prefix . 'nodegroups` SET ';
		$sets = array();
		$status = false;
		$values = array();

		list($binds, $sets, $values) =
			$this->prepFields($fields, $input);

		$query .= implode(', ', $sets);

		$binds .= 's';
		$values[] = stripAt($input['nodegroup']);
		$query .= ' WHERE `nodegroup` = ? LIMIT 1';

		$status = $this->queryWrite($query, $binds, $values);
		if($status === 0 || $status === 1) {
			return $this->getNodegroupByID($input['nodegroup']);
		}

		if($status > 1) {
			$this->error = 'More than one row updated';
			if($this->query_on_error) {
				$this->error .= ': ' . $query;
			}
		}

		return false;
	}

	/**
	 * Set the children nodegroups for given nodegroup
	 * Anything not in $children will be removed
	 * @param string $nodegroup
	 * @param array $children
	 * @param array $inherited
	 * @return bool
	 */
	public function setChildren($nodegroup, $children, $inherited) {
		$adds = array();
		$dels = array($nodegroup);
		$binds_add = '';
		$binds_del = 's';
		$query_add = 'INSERT IGNORE INTO `' .
			$this->prefix . 'children` ' .
			'(`nodegroup`, `child`, `inherited`) VALUES ';
		$query_del = 'DELETE FROM `' . $this->prefix . 'children` ' .
			'WHERE `nodegroup` = ?';
		$sets_add = array();
		$sets_del = array();
		$status = false;

		$nodegroup = stripAt($nodegroup);

		if(!empty($children)) {
			while(list($junk, $child) = each($children)) {
				$adds[] = $nodegroup;
				$adds[] = stripAt($child);
				$binds_add .= 'ss';
				$binds_del .= 's';
				$dels[] = stripAt($child);
				$sets_del[] = '?';

				$set = '?, ?, ';

				if(in_array($child, $inherited)) {
					$set .= '1';
				} else {
					$set .= '0';
				}

				$sets_add[] = '(' . $set . ')';
			}
			reset($children);

			$query_add .= implode(', ', $sets_add);

			$status = $this->queryWrite($query_add,
				$binds_add, $adds);
			if($status === false) {
				return false;
			}

			$query_del .= ' AND `child` NOT IN ';
			$query_del .= '(' . implode(', ', $sets_del) . ')';
		}

		$status = $this->queryWrite($query_del, $binds_del, $dels);
		if($status !== false) {
			return true;
		}

		return false;
	}
}

?>
