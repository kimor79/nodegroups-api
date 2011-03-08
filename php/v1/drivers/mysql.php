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
	 * Get children for given nodegroup
	 * @param string $nodegroup
	 * @return mixed array with details (which may be empty) or false
	 */
	public function getChildren($nodegroup) {
		$nodegroup = $this->stripAt($nodegroup);

		$query = sprintf("SELECT `child` FROM `%sparent_child`",
			$this->prefix);
		$query .= ' WHERE `parent` = ?';

		$data = $this->queryRead($query, array('s', &$nodegroup));
		if(is_array($data)) {
			$details = array();
			while(list($junk, $record) = each($data)) {
				$details[] = $record['child'];
			}

			return $details;
		}

		return false;
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
		$fields_join = '';
		$limit = false;
		$refs = array();
		$query_fields = array();
		$query_nodegroups = array();
		$query_nodes = array();
		$questions_eq = array();

		$fields = array(
			'description' => '`description`',
			'expression' => '`expression`',
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
			$fields['order'] = 'IFNULL(`order`, 50) AS `order`';

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
				$fields_join = ' LEFT JOIN `nodegroups`';
				$fields_join .= ' USING (`nodegroup`)';

				$query_main .= sprintf(", %s",
					implode(', ', $query_fields));
			}
		}

		$query = sprintf(" FROM `%snodes`", $this->prefix);
		$query .= $app_join . $fields_join;
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
	 * Get parents for given nodegroup
	 * @param string $nodegroup
	 * @return mixed array with details (which may be empty) or false
	 */
	public function getParents($nodegroup) {
		$nodegroup = $this->stripAt($nodegroup);

		$query = sprintf("SELECT `parent` FROM `%sparent_child`",
			$this->prefix);
		$query .= ' WHERE `child` = ?';

		$data = $this->queryRead($query, array('s', &$nodegroup));
		if(is_array($data)) {
			$details = array();
			while(list($junk, $record) = each($data)) {
				$details[] = $record['parent'];
			}

			return $details;
		}

		return false;
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
		if($status == 1) {
			return true;
		}

		if($status === 0) {
			$this->error = 'No rows updated';
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
