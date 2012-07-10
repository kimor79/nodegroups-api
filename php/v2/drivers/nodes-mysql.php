<?php

include_once 'api_producer/v2/drivers/mysql.php';

class NodegroupsAPIV2DriverNodesMySQL extends APIProducerV2DriverMySQL {

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
	 * Get the list of nodes for given nodegroup
	 * @param string $nodegroup
	 * @return array nodes, inherited
	 */
	public function getNodesFromNodegroup($nodegroup) {
		$inherited = array();
		$nodes = array();

		$nodegroup = stripAt($nodegroup);

		$data = $this->select(array(
			'_binds' => 's',
			'_values' => array($nodegroup),
			'from' => '`' . $this->prefix . 'nodes`',
			'where' => '`nodegroup` = ?',
		));

		while(list($junk, $datum) = each($data)) {
			$nodes[] = $datum['node'];

			if($datum['inherited']) {
				$inherited[] = $datum['node'];
			}
		}

		return array(
			'inherited' => $inherited,
			'nodes' => $nodes,
		);
	}

	/**
	 * Set the nodes for given nodegroup
	 * Anything not in $nodes will be removed
	 * @param string $nodegroup
	 * @param array $nodes
	 * @param array $inherited
	 * @return bool
	 */
	public function setNodes($nodegroup, $nodes, $inherited) {
		$nodegroup = stripAt($nodegroup);

		$adds = array();
		$dels = array_merge((array) $nodegroup, $nodes);
		$binds_add = '';
		$binds_del = 's';
		$query_add = 'INSERT IGNORE INTO `' .
			$this->prefix . 'nodes` ' .
			'(`nodegroup`, `node`, `inherited`) VALUES ';
		$query_del = 'DELETE FROM `' . $this->prefix . 'nodes` ' .
			'WHERE `nodegroup` = ?';
		$sets_add = array();
		$sets_del = array();
		$status = false;

		if(!empty($nodes)) {
			while(list($junk, $node) = each($nodes)) {
				$adds[] = $nodegroup;
				$adds[] = $node;
				$binds_add .= 'ss';
				$binds_del .= 's';
				$sets_del[] = '?';

				$set = '?, ?, ';

				if(in_array($node, $inherited)) {
					$set .= '1';
				} else {
					$set .= '0';
				}

				$sets_add[] = '(' . $set . ')';
			}
			reset($nodes);

			$query_add .= implode(', ', $sets_add);

			$status = $this->queryWrite($query_add,
				$binds_add, $adds);
			if($status === false) {
				return false;
			}

			$query_del .= ' AND `node` NOT IN ';
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
