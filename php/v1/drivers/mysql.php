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

class NodegroupsApiDriverMySQL {

	protected $count = 0;
	protected $error = '';
	private $mysql;
	private $prefix = '';
	protected $slave_okay = false;

	public function __construct($slave_okay = false) {
		global $config;

		$this->slave_okay = $slave_okay;

		$database = $this->getConfig('database', 'nodegroups');
		$host = $this->getConfig('host', 'localhost');
		$password = $this->getConfig('password', '');
		$user = $this->getConfig('user', '');

		$this->prefix = $this->getConfig('prefix', '');

		$this->mysql = new mysqli($host, $user, $password, $database);

		if(mysqli_connect_errno()) {
			throw new Exception(mysqli_connect_error());
		}
	}

	public function __deconstruct() {
		$this->mysql->close();
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

		$status = $this->queryWrite($query, $binds);
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
		$nodegroup = $this->stripAt($nodegroup);

		$query = 'DELETE FROM `' . $this->prefix . 'nodegroups` WHERE ';
		$query .= '`nodegroup` = ?';

		$status = $this->queryWrite($query, array('s', &$nodegroup));
		if($status !== false) {
			return true;
		}
				
		if($st->errno) {
			$this->error = $st->error;
		}

		return false;
	}

	/**
	 * Return last error
	 * @return string
	 */
	public function error() {
		return $this->error;
	}

	/** Get a config value
	 * @param string $key
	 * @param string $default
	 * @return string
	 */
	protected function getConfig($key = '', $default = '') {
		global $config;

		if(!array_key_exists('mysql', $config)) {
			return $default;
		}

		$type = 'rw' . $key;
		if($this->slave_okay) {
			$type = 'ro' . $key;
		}

		if(array_key_exists($type, $config['mysql'])) {
			return $config['mysql'][$type];
		}

		if(array_key_exists($key, $config['mysql'])) {
			return $config['mysql'][$key];
		}

		return $default;
	}

	/**
	 * Get the total from the last query
	 * @return int
	 */
	public function getCount() {
		return $this->count;
	}

	/**
	 * Get a nodegroup
	 * @param string $nodegroup
	 * @return mixed array with details (which may be empty) or false
	 */
	public function getNodegroup($nodegroup) {
		$nodegroup = $this->stripAt($nodegroup);

		$query = 'SELECT * FROM `' . $this->prefix . 'nodegroups`';
		$query .= ' WHERE `nodegroup` = ?';

		$data = $this->queryRead($query, array('s', &$nodegroup));
		if(is_array($data)) {
			$details = array();
			while(list($junk, $record) = each($data)) {
				$details = $record;
			}

			return $details;
		}

		return false;
	}

	/**
	 * Get list of nodegroups for a node
	 * @param array $nodes array('re' => array(), 'eq' => array())
	 * @param string $app
	 * @param array $options sort col, start, end
	 * @return mixed array of nodes (which may be empty) or false
	 */
	public function getNodegroupsFromNode($input,
			$app = '', $options = array()) {
		$app_join = '';
		$app_order = '';
		$app_where = '';
		$binds = '';
		$limit = false;
		$refs = array();
		$query_fields = array();
		$query_nodegroups = array();
		$query_nodes = array();
		$questions_eq = array();

		$fields = array(
			'description' => '`description`',
			'expression' => '`expression`',
			'order' => 'IFNULL(`order`, 50) AS `order`',
		);

		while(list($node, $junk) = each($input['eq'])) {
			$binds .= 's';
			$refs[] = &$input['eq'][$node];
			$questions_eq[] = '?';
		}

		if(!empty($questions_eq)) {
			$query_nodes[] = sprintf("`node` IN (%s)",
				implode(',', $questions_eq));
		}

		while(list($node, $junk) = each($input['re'])) {
			$binds .= 's';
			$refs[] = &$input['re'][$node];
			$query_nodes[] = '`node` REGEXP ?';
		}

		if($app) {
			$binds .= 's';
			array_unshift($refs, &$app);
			$app_join = ' LEFT JOIN `order` USING (`nodegroup`)';
			$app_order = 'IFNULL(`order`, 50), ';
			$app_where = '(`app` IS NULL OR `app` = ?) AND ';
		}

		$query_count = 'SELECT COUNT(DISTINCT(`nodes`.`nodegroup`))';
		$query_count .= ' AS `count`';

		$query_main = 'SELECT DISTINCT(`nodes`.`nodegroup`)';
		$query_main .= ' AS `nodegroup`';

		if(array_key_exists('outputFields', $options)) {
			foreach($fields as $key => $field) {
				if($options['outputFields'][$key]) {
					$query_fields[] = $field;
				}
			}

			if(!empty($query_fields)) {
				$query_main .= sprintf(", %s",
					implode(', ', $query_fields));
			}
		}

		$query = sprintf(" FROM `%snodes`", $this->prefix);
		$query .= $app_join;
		$query .= sprintf(" WHERE %s(%s)", $app_where,
			implode(' OR ', $query_nodes));

		if(array_key_exists('nodegroup_re', $options)) {
			while(list($group, $junk) =
					each($options['nodegroup_re'])) {
				$binds .= 's';
				$refs[] = &$options['nodegroup_re'][$group];
				$query_nodegroups[] = '`nodegroup` REGEXP ?';
			}

			$query .= sprintf(" AND (%s)",
				implode(' OR ', $query_nodegroups));
		}

		$query_count .= $query;
		$query_main .= $query;

		if(array_key_exists('sortDir', $options)) {
			$query_main .= sprintf(" ORDER BY %s`nodegroup` %s",
				$app_order, $options['sortDir']);
		}

		if(array_key_exists('startIndex', $options)) {
			$limit = true;
			$query_main .= sprintf(" LIMIT %d, %d",
				$options['startIndex'],
				($options['numResults']) ?
					$options['numResults'] :
					'18446744073709551615');
		}

		array_unshift($refs, $binds);

		$data = $this->queryRead($query_main, $refs);
		if(is_array($data)) {
			$nodegroups = array();
			while(list($junk, $record) = each($data)) {
				$nodegroups[] = $record;
			}

			if($limit) {
				$count = $this->queryRead($query_count, $refs);
				if(is_array($count)) {
					foreach($count as $record) {
						$this->count = $record['count'];
					}
				}
			} else {
				$this->count = count($nodegroups);
			}

			return $nodegroups;
		}

		return false;
	}

	/**
	 * Get a nodes from a nodegroup
	 * @param array $nodegroups
	 * @param array $options sort col, start, end
	 * @return mixed array of nodes (which may be empty) or false
	 */
	public function getNodesFromNodegroup($input, $options = array()) {
		$binds = '';
		$limit = false;
		$refs = array();
		$questions = array();

		while(list($key, $group) = each($input)) {
			$input[$key] = $this->stripAt($group);
			$binds .= 's';
			$refs[] = &$input[$key];
			$questions[] = '?';
		}

		$query_count = 'SELECT COUNT(DISTINCT(`node`)) AS `count`';
		$query_main = 'SELECT DISTINCT(`node`) AS `node`';

		$query = sprintf(" FROM `%snodes`", $this->prefix);
		$query .= sprintf(" WHERE `nodegroup` IN (%s)",
			implode(',', $questions));

		$query_count .= $query;
		$query_main .= $query;

		if(array_key_exists('sortDir', $options)) {
			$query_main .= sprintf(" ORDER BY `node` %s",
				$options['sortDir']);
		}

		if(array_key_exists('startIndex', $options)) {
			$limit = true;
			$query_main .= sprintf(" LIMIT %d, %d",
				$options['startIndex'],
				($options['numResults']) ?
					$options['numResults'] :
					'18446744073709551615');
		}

		array_unshift($refs, $binds);

		$data = $this->queryRead($query_main, $refs);
		if(is_array($data)) {
			$nodes = array();
			while(list($junk, $record) = each($data)) {
				$nodes[] = $record['node'];
			}

			if($limit) {
				$count = $this->queryRead($query_count, $refs);
				if(is_array($count)) {
					foreach($count as $record) {
						$this->count = $record['count'];
					}
				}
			} else {
				$this->count = count($nodegroups);
			}

			return $nodes;
		}

		return false;
	}

	/**
	 * Perform a read-only query
	 * @param string $query
	 * @param array $binds
	 * @return mixed records or false
	 */
	protected function queryRead($query, $binds) {
		$st = $this->mysql->prepare($query);
		if(!$st) {
			$this->error = $this->mysql->error;
			return false;
		}

		if(!call_user_func_array(array($st, 'bind_param'), $binds)) {
			if($st->errno) {
				$this->error = $st->error;
			}

			$st->close();
			return false;
		}

		if(!$st->execute()) {
			if($st->errno) {
				$this->error = $st->error;
			}

			$st->close();
			return false;
		}

		if(!$st->store_result()) {
			if($st->errno) {
				$this->error = $st->error;
			}

			$st->close();
			return false;
		}

		$result = $st->result_metadata();
		if(!$result) {
			if($st->errno) {
				$this->error = $st->error;
			}

			$st->close();
			return false;
		}

		$columns = array();
		foreach($result->fetch_fields() as $field) {
			$columns[] = &$fields[$field->name];
		}

		if(call_user_func_array(array($st, 'bind_result'), $columns)) {
			$records = array();
			while($st->fetch()) {
				$details = array();
				foreach($fields as $field => $value) {
					$details[$field] = $value;
				}

				$records[] = $details;
			}

			$st->close();
			return $records;
		}

		if($st->errno) {
			$this->error = $st->error;
		}

		$st->close();
		return false;
	}

	/**
	 * Perform a write query
	 * @param string $query
	 * @param array $binds
	 * @return mixed affected rows or false
	protected function queryWrite($query, $binds) {
		$st = $this->mysql->prepare($query);
		if(!$st) {
			$this->error = $this->mysql->error;
			return false;
		}

		if(!call_user_func_array(array($st, 'bind_param'), $binds)) {
			if($st->execute()) {
				if(is_numeric($st->affected_rows)) {
					$rows = $st->affected_rows;

					$st->close();
					return $rows;
				}
			}
		}

		if($st->errno) {
			$this->error = $st->error;
		}

		$st->close();
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
			if($status == false) {
				return false;
			}
		}

		$query_delete = sprintf("DELETE FROM `%sparent_child` WHERE ",
			$this->prefix);
		$query_delete .= '`parent` = ?';

		if(!empty($save)) {
			$query_delete .= sprintf(" AND `child` NOT IN (%s)",
				implode(',', $save_questions));
		}

		array_unshift($save, $binds . 's', &$nodegroup);

		$status = $this->queryWrite($query_delete, $save);
		if($status != false) {
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
			if($status == false) {
				return false;
			}
		}

		$query_delete = sprintf("DELETE FROM `%snodes` WHERE ",
			$this->prefix);
		$query_delete .= '`nodegroup` = ?';

		if(!empty($save)) {
			$query_delete .= sprintf(" AND `node` NOT IN (%s)",
				implode(',', $save_questions));
		}

		array_unshift($save, $binds . 's', &$nodegroup);

		$status = $this->queryWrite($query_delete, $save);
		if($status != false) {
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
