<?php

/**

Copyright (c) 2010, Kimo Rosenbaum and contributors
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:
    * Redistributions of source code must retain the above copyright
      notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright
      notice, this list of conditions and the following disclaimer in the
      documentation and/or other materials provided with the distribution.
    * Neither the name of the owner nor the names of its contributors
      may be used to endorse or promote products derived from this
      software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

**/

require_once('api_producer/v1/drivers/mysql.php');

class NodegroupsApiDriverMySQL extends ApiProducerDriverMySQL {

	protected $count = 0;

	public function __construct($slave_okay = false) {
		global $config;

		$mconfig = array();

		if(array_key_exists('mysql', $config)) {
			$mconfig = $config['mysql'];
		}

		if(!array_key_exists('database', $mconfig)) {
			$mconfig['database'] = 'nodegroups';
		}

		parent::__construct($slave_okay, $mconfig);
	}

	public function __deconstruct() {
		parent::__deconstruct();
	}

	/**
	 * Add a nodegroup
	 * @param string $nodegroup
	 * @param array $details
	 * @return bool
	 */
	public function addNodegroup($nodegroup = '', $details = array()) {
		$nodegroup = $this->stripAt($nodegroup);

		$binds = 's';
		$refs = array(&$nodegroup);

		$query = 'INSERT INTO `' . $this->prefix . 'nodegroups` SET ';
		$query .= '`nodegroup` = ?';

		foreach($details as $key => $value) {
			$query .= sprintf(", `%s` = ?", $key);
			$refs[$key] = &$details[$key];
			$binds .= 's';
		}

		array_unshift($refs, $binds);

		$status = $this->queryWrite($query, $refs);
		if($status == 1) {
			return true;
		}

		if($status > 1) {
			$this->error = 'More than one row added';
		}

		return false;
	}

	/**
	 * Get the total from the last query
	 * @return int
	 */
	public function count() {
		return $this->count;
	}

	/**
	 * Delete a nodegroup
	 * @param string $nodegroup
	 * @return bool
	 */
	public function deleteNodegroup($nodegroup) {
		$nodegroup = $this->stripAt($nodegroup);

		$query = 'DELETE FROM `' . $this->prefix . 'nodegroups` WHERE ';
		$query .= '`nodegroup` = ?';

		$status = $this->queryWrite($query, array('s', &$nodegroup));
		if($status == 1) {
			return true;
		}

		if($status === 0) {
			$this->error = 'No rows deleted';
		}

		if($status > 1) {
			$this->error = 'More than one row deleted';
		}
				
		return false;
	}

	/**
	 * Get a nodegroup
	 * @param string $nodegroup
	 * @return mixed array with details (which may be empty) or false
	 */
	public function getNodegroup($nodegroup) {
		$nodegroup = $this->stripAt($nodegroup);

		return $this->select(array(
			'_binds' => 's',
			'_refs' => array(&$nodegroup),
			'from' => sprintf("`%snodegroups`", $this->prefix),
			'where' => '`nodegroup` = ?',
		));
	}

	/**
	 * Get nodes from a nodegroup
	 * @param string $nodegroup
	 * @return mixed array with nodes (which may be empty) or false
	 */
	public function getNodesFromNodegroup($nodegroup) {
		$data = $this->listNodesFromNodegroups(array(
			'eq' => array($nodegroup)));

		if(is_array($data)) {
			$records = array();
			while(list($junk, $node) = each($data)) {
				$records[] = $node['node'];
			}

			return $records;
		}

		return $data;
	}

	/**
	 * List children for given nodegroup
	 * @param string $nodegroup
	 * @return mixed array with details (which may be empty) or false
	 */
	public function listChildren($nodegroup) {
		$nodegroup = $this->stripAt($nodegroup);

		return $this->select(array(
			'_binds' => 's',
			'_refs' => array(&$nodegroup),
			'from' => sprintf("`%sparent_child`", $this->prefix),
			'select' => '`child`',
			'where' => '`parent` = ?',
		));
	}

	/**
	 * List nodegroups
	 * @param array $input 'field' => array('re' => array(), 'eq' => array()
	 * @param array $options sort col, start, end
	 * @return mixed array of nodegroups (which may be empty) or false
	 */
	public function listNodegroups($input, $options = array()) {
		$binds = '';
		$fields = array(
			'description' => '`description`',
			'nodegroup' => '`nodegroup`',
			'expression' => '`expression`',
		);
		$groups = array();
		$query = array(
			'from' => sprintf("`%snodegroups`", $this->prefix),
		);
		$refs = array();
		$select = array();
		$select_count = '';
		$where = array();

		$this->count = 0;

		foreach($fields as $name => $field) {
			if($options['outputFields'][$name]) {
				$select[$name] = $field;
			}

			if(!array_key_exists($name, $input)) {
				continue;
			}

			$questions = array();
			$t_where = array();

			if(!array_key_exists('eq', $input[$name])) {
				$input[$name]['eq'] = array();
			}

			if(!array_key_exists('re', $input[$name])) {
				$input[$name]['re'] = array();
			}

			while(list($key, $val) = each($input[$name]['eq'])) {
				if($name == 'nodegroup') {
					$input[$name]['eq'][$key] =
						$this->stripAt($val);
				}

				$binds .= 's';
				$refs[] = &$input[$name]['eq'][$key];
				$questions[] = '?';
			}

			if(!empty($questions)) {
				$t_where[] = sprintf("%s IN (%s)", $field,
					implode(', ', $questions));
					
			}

			while(list($key, $junk) = each($input[$name]['re'])) {
				$binds .= 's';
				$refs[] = &$input[$name]['re'][$key];
				$t_where[] = sprintf("%s REGEXP ?", $field);
			}

			$where[] = sprintf("(%s)", implode(' OR ', $t_where));
		}

		if(!empty($select)) {
			$select['nodegroup'] = '`nodegroup`';
		}

		if(array_key_exists('sortDir', $options)) {
			$query['order'] .= ' ' . $options['sortDir'];
		}

		$query['_binds'] = $binds;
		$query['_refs'] = $refs;
		$query['select'] = implode(', ', $select);
		$query['where'] = implode(' AND ', $where);

		if($options['startIndex']) {
			$query['limit'][0] = $options['startIndex'];
		}

		if($options['numResults']) {
			$query['limit'][1] = $options['numResults'];
		}

		$records = $this->select($query);
		if(is_array($records)) {
			if(array_key_exists('limit', $query)) {
				unset($query['limit']);
				unset($query['order']);
				$query['select'] = 'COUNT(`nodegroup`)';
				$query['select'] .= ' AS `count`';

				$total = $this->select($query);
				if(is_array($total)) {
					foreach($total as $count) {
						$this->count = $count['count'];
						break;
					}
				}
			} else {
				$this->count = count($records);
			}
		}

		return $records;
	}

	/**
	 * List nodegroups for a node
	 * @param array $nodes array('re' => array(), 'eq' => array())
	 * @param string $app
	 * @param array $options sort col, start, end
	 * @return mixed array of nodes (which may be empty) or false
	 */
	public function listNodegroupsFromNodes($input,
			$app = '', $options = array()) {
		$binds = '';
		$fields = array(
			'description' => '`description`',
			'expression' => '`expression`',
		);
		$nodegroups = array();
		$nodes = array();
		$query = array(
			'from' => sprintf("`%snodes`", $this->prefix),
			'order' => '`nodegroup`',
		);
		$questions = array();
		$refs = array();
		$select = array();
		$select_count = '';
		$where = array();

		$this->count = 0;

		$select['nodegroup'] = 'DISTINCT(';
		$select['nodegroup'] .= sprintf("`%snodes`.`nodegroup`",
			$this->prefix);
		$select['nodegroup'] .= ') AS `nodegroup`';

		if(!array_key_exists('eq', $input)) {
			$input['eq'] = array();
		}

		while(list($node, $junk) = each($input['eq'])) {
			$binds .= 's';
			$refs['eq' . $node] = &$input['eq'][$node];
			$questions[] = '?';
		}

		if(!empty($questions)) {
			$nodes[] = sprintf("`node` IN (%s)",
				implode(', ', $questions));
		}

		if(!array_key_exists('re', $input)) {
			$input['re'] = array();
		}

		while(list($node, $junk) = each($input['re'])) {
			$binds .= 's';
			$refs['re' . $node] = &$input['re'][$node];
			$nodes[] = '`node` REGEXP ?';
		}

		$where['nodes'] = sprintf("(%s)", implode(' OR ', $nodes));

		if($app) {
			$query['order'] = 'IFNULL(`order`, 50), ' .
				$query['order'];
			$fields['order'] .= 'IFNULL(`order`, 50) AS `order`';

			$query['from'] .= sprintf(" LEFT JOIN `%sorder`",
				$this->prefix);
			$query['from'] .= ' USING (`nodegroup`)';

			$binds .= 's';
			$refs['app'] = &$app;
			$where['app'] = '(`app` IS NULL OR `app` = ?)';
		}

		if(array_key_exists('nodegroup_re', $options)) {
			while(list($group, $junk) =
					each($options['nodegroup_re'])) {
				$binds .= 's';
				$refs[$group] =
					&$options['nodegroup_re'][$group];

				$nodegroups[] = '`nodegroup` REGEXP ?';
			}

			if(!empty($nodegroups)) {
				$where['nodegroup'] = sprintf("(%s)",
					implode(', ', $nodegroups));
			}
		}

		if(array_key_exists('outputFields', $options)) {
			foreach($fields as $key => $field) {
				if($options['outputFields'][$key]) {
					$select[$key] = $field;
				}
			}

			$query['from'] .= sprintf(" LEFT JOIN `%snodegroups`",
				$this->prefix);
			$query['from'] .= ' USING (`nodegroup`)';
		}

		if(array_key_exists('sortDir', $options)) {
			$query['order'] .= ' ' . $options['sortDir'];
		}

		$query['_binds'] = $binds;
		$query['_refs'] = $refs;
		$query['select'] = implode(', ', $select);
		$query['where'] = implode(' AND ', $where);

		if($options['startIndex']) {
			$query['limit'][0] = $options['startIndex'];
		}

		if($options['numResults']) {
			$query['limit'][1] = $options['numResults'];
		}

		$records = $this->select($query);
		if(is_array($records)) {
			if(array_key_exists('limit', $query)) {
				unset($query['limit']);
				unset($query['order']);
				$query['select'] = 'COUNT(DISTINCT(';
				$query['select'] .= sprintf("`%snodes`",
					$this->prefix);
				$query['select'] .= '.`nodegroup`)) AS `count`';

				$total = $this->select($query);
				if(is_array($total)) {
					foreach($total as $count) {
						$this->count = $count['count'];
						break;
					}
				}
			} else {
				$this->count = count($records);
			}
		}

		return $records;
	}

	/**
	 * List nodes from a nodegroup
	 * @param array $nodegroups
	 * @param array $options sort col, start, end
	 * @return mixed array of nodes (which may be empty) or false
	 */
	public function listNodesFromNodegroups($input, $options = array()) {
		$binds = '';
		$fields = array(
			'node' => '`node`',
		);
		$nodegroups = array();
		$nodes = array();
		$query = array(
			'from' => sprintf("`%snodes`", $this->prefix),
			'order' => '`node`',
		);
		$questions = array();
		$refs = array();
		$select = array();
		$select_count = '';
		$where = array();

		$this->count = 0;

		$select['nodegroup'] = 'DISTINCT(`node`) AS `node`';

		if(!array_key_exists('eq', $input)) {
			$input['eq'] = array();
		}

		while(list($key, $group) = each($input['eq'])) {
			$input['eq'][$key] = $this->stripAt($group);
			$binds .= 's';
			$refs['eq' . $key] = &$input['eq'][$key];
			$questions[] = '?';
		}

		if(!empty($questions)) {
			$nodegroups[] = sprintf("`nodegroup` IN (%s)",
				implode(', ', $questions));
		}

		if(!array_key_exists('re', $input)) {
			$input['re'] = array();
		}

		while(list($key, $group) = each($input['re'])) {
			$input['re'][$key] = $this->stripAt($group);
			$binds .= 's';
			$refs['re' . $key] = &$input['re'][$key];
			$nodegroups[] = '`nodegroup` REGEXP ?';
		}

		$where['nodegroups'] = sprintf("(%s)",
			implode(' OR ', $nodegroups));

		if(array_key_exists('node_re', $options)) {
			while(list($node, $junk) = each($options['node_re'])) {
				$binds .= 's';
				$refs[$node] =
					&$options['node_re'][$node];

				$nodes[] = '`node` REGEXP ?';
			}

			if(!empty($nodes)) {
				$where['nodes'] = sprintf("(%s)",
					implode(', ', $nodes));
			}
		}

		if(array_key_exists('outputFields', $options)) {
			foreach($fields as $key => $field) {
				if($options['outputFields'][$key]) {
					$select[$key] = $field;
				}
			}
		}

		if(array_key_exists('sortDir', $options)) {
			$query['order'] .= ' ' . $options['sortDir'];
		}

		$query['_binds'] = $binds;
		$query['_refs'] = $refs;
		$query['select'] = implode(', ', $select);
		$query['where'] = implode(' AND ', $where);

		if($options['startIndex']) {
			$query['limit'][0] = $options['startIndex'];
		}

		if($options['numResults']) {
			$query['limit'][1] = $options['numResults'];
		}

		$records = $this->select($query);
		if(is_array($records)) {
			if(array_key_exists('limit', $query)) {
				unset($query['limit']);
				unset($query['order']);
				$query['select'] = 'COUNT(DISTINCT(`node`))';
				$query['select'] .= ' AS `count`';

				$total = $this->select($query);
				if(is_array($total)) {
					foreach($total as $count) {
						$this->count = $count['count'];
						break;
					}
				}
			} else {
				$this->count = count($records);
			}
		}

		return $records;
	}

	/**
	 * List parents for given nodegroup
	 * @param string $nodegroup
	 * @return mixed array with details (which may be empty) or false
	 */
	public function listParents($nodegroup) {
		$nodegroup = $this->stripAt($nodegroup);

		return $this->select(array(
			'_binds' => 's',
			'_refs' => array(&$nodegroup),
			'from' => sprintf("`%sparent_child`", $this->prefix),
			'select' => '`parent`',
			'where' => '`child` = ?',
		));
	}

	/**
	 * Modify a nodegroup
	 * @param string $nodegroup
	 * @param array $details
	 * @return bool
	 */
	public function modifyNodegroup($nodegroup = '', $details = array()) {
		$nodegroup = $this->stripAt($nodegroup);

		$binds = 's';
		$refs = array();
		$query_set = array();

		foreach($details as $key => $value) {
			$query_set[] = sprintf("`%s` = ?", $key);
			$refs[$key] = &$details[$key];
			$binds .= 's';
		}

		$query = 'UPDATE `' . $this->prefix . 'nodegroups` SET ';
		$query .= implode(', ', $query_set);
		$query .= ' WHERE `nodegroup` = ?';

		$refs[] = &$nodegroup;
		array_unshift($refs, $binds);

		$status = $this->queryWrite($query, $refs);
		if($status == 1 || $status === 0) {
			return true;
		}

		if($status > 1) {
			$this->error = 'More than one row modified';
		}

		return false;
	}

	/**
	 * Set the list of nodegroups included in a nodegroup
	 * @param string $nodegroup
	 * @param array $children
	 * @return bool
	 */
	public function setChildren($nodegroup = '', $children = array()) {
		$nodegroup = $this->stripAt($nodegroup);

		$add = array();
		$add_questions = array();
		$binds = '';
		$save = array();
		$save_questions = array();

		while(list($key, $child) = each($children)) {
			$children[$key] = $this->stripAt($child);
			$add[] = &$nodegroup;
			$add[] = &$children[$key];
			$add_questions[] = '(?, ?)';
			$binds .= 's';
			$save[] = &$children[$key];
			$save_questions[] = '?';
		}

		if(!empty($add)) {
			$query_add = 'INSERT IGNORE INTO';
			$query_add .= sprintf(" `%sparent_child`",
				$this->prefix);
			$query_add .= ' (`parent`, `child`) VALUES ';
			$query_add .= implode(', ', $add_questions);

			array_unshift($add, $binds . $binds);

			$status = $this->queryWrite($query_add, $add);
			if($status === false) {
				return false;
			}
		}

		$query_delete = sprintf("DELETE FROM `%sparent_child` WHERE ",
			$this->prefix);
		$query_delete .= '`parent` = ?';

		if(empty($save)) {
			$save[] = 's';
			$save[] = &$nodegroup;
		} else {
			$query_delete .= sprintf(" AND `child` NOT IN (%s)",
				implode(',', $save_questions));

			array_unshift($save, $binds . 's', &$nodegroup);
		}

		$status = $this->queryWrite($query_delete, $save);
		if($status !== false) {
			return true;
		}

		return false;
	}

	/**
	 * Set nodes for given nodegroup
	 * @param string $nodegroup
	 * @param array $nodes
	 * @return bool
	 */
	public function setNodes($nodegroup = '', $nodes = array()) {
		$nodegroup = $this->stripAt($nodegroup);

		$add = array();
		$add_questions = array();
		$binds = '';
		$save = array();
		$save_questions = array();

		while(list($key, $node) = each($nodes)) {
			$add[] = &$nodegroup;
			$add[] = &$nodes[$key];
			$add_questions[] = '(?, ?)';
			$binds .= 's';
			$save[] = &$nodes[$key];
			$save_questions[] = '?';
		}

		if(!empty($add)) {
			$query_add = 'INSERT IGNORE INTO';
			$query_add .= sprintf(" `%snodes`",
				$this->prefix);
			$query_add .= ' (`nodegroup`, `node`) VALUES ';
			$query_add .= implode(', ', $add_questions);

			array_unshift($add, $binds . $binds);

			$status = $this->queryWrite($query_add, $add);
			if($status === false) {
				return false;
			}
		}

		$query_delete = sprintf("DELETE FROM `%snodes` WHERE ",
			$this->prefix);
		$query_delete .= '`nodegroup` = ?';

		if(empty($save)) {
			$save[] = 's';
			$save[] = &$nodegroup;
		} else {
			$query_delete .= sprintf(" AND `node` NOT IN (%s)",
				implode(',', $save_questions));

			array_unshift($save, $binds . 's', &$nodegroup);
		}

		$status = $this->queryWrite($query_delete, $save);
		if($status !== false) {
			return true;
		}

		return false;
	}

	/**
	 * Strip the @ from a nodegroup name
	 * @param string $nodegroup
	 * @return string
	 */
	private function stripAt($nodegroup) {
		if(substr($nodegroup, 0, 1) === '@') {
			return substr($nodegroup, 1);
		}

		return $nodegroup;
	}
}

?>
