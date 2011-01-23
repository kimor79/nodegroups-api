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

class NodegroupsApiDriver {

	protected $error = '';
	private $mysql;
	private $prefix = '';

	public function __construct($slave_okay = false) {
		global $config;

		$database = 'nodegroups';
		$host = 'localhost';
		$password = '';
		$type = 'rw';
		$user = '';

		if($slave_okay) {
			$type = 'ro';
		}

		if(array_key_exists('mysql', $config)) {
			if(array_key_exists('database', $config['mysql'])) {
				$database = $config['mysql']['database'];
			}

			if(array_key_exists($type . '_database', $config['mysql'])) {
				$database = $config['mysql'][$type . '_database'];
			}

			if(array_key_exists('host', $config['mysql'])) {
				$host = $config['mysql']['host'];
			}

			if(array_key_exists($type . '_host', $config['mysql'])) {
				$host = $config['mysql'][$type . '_host'];
			}

			if(array_key_exists('password', $config['mysql'])) {
				$password = $config['mysql']['password'];
			}

			if(array_key_exists($type . '_password', $config['mysql'])) {
				$password = $config['mysql'][$type . '_password'];
			}

			if(array_key_exists('prefix', $config['mysql'])) {
				$this->prefix = $config['mysql']['prefix'];
			}

			if(array_key_exists($type . '_prefix', $config['mysql'])) {
				$prefix = $config['mysql'][$type . '_prefix'];
			}

			if(array_key_exists('user', $config['mysql'])) {
				$user = $config['mysql']['user'];
			}

			if(array_key_exists($type . '_user', $config['mysql'])) {
				$user = $config['mysql'][$type . '_user'];
			}
		}

		$this->mysql = new mysqli($host, $user, $password, $database);

		if(mysqli_connect_errno()) {
			throw new Exception(mysqli_connect_error());
		}
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

		$st = $this->mysql->prepare($query);
		if(!$st) {
			$this->error = $this->mysql->error;
			return false;
		}

		array_unshift($refs, $binds);

		if(call_user_func_array(array($st, 'bind_param'), $refs)) {
			if($st->execute()) {
				if($st->affected_rows == 1) {
					return true;
				} else {
					$this->error = 'More than one row added';
					return false;
				}
			}
		}

		if($st->errno) {
			$this->error = $st->error;
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

		$st = $this->mysql->prepare($query);
		if(!$st) {
			$this->error = $this->mysql->error();
			return false;
		}

		if($st->bind_param('s', &$nodegroup)) {
			if($st->execute()) {
				if($st->affected_rows > 0) {
					return true;
				}
			}
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

	/**
	 * Get a nodegroup
	 * @param string $nodegroup
	 * @return mixed array with details (which may be empty) or false
	 */
	public function getNodegroup($nodegroup) {
		$nodegroup = $this->stripAt($nodegroup);

		$query = 'SELECT * FROM `' . $this->prefix . 'nodegroups`';
		$query .= ' WHERE `nodegroup` = ?';

		$st = $this->mysql->prepare($query);
		if(!$st) {
			$this->error = $this->mysql->error;
			return false;
		}

		if($st->bind_param('s', &$nodegroup)) {
			if($st->execute()) {
				if($st->store_result()) {
					$result = $st->result_metadata();
					if($result) {
						$columns = array();
						foreach($result->fetch_fields() as $field) {
							$columns[] = &$fields[$field->name];
						}

						if(call_user_func_array(array($st, 'bind_result'), $columns)) {
							while($st->fetch()) {
								$details = array();
								foreach($fields as $field => $value) {
									$details[$field] = $value;
								}

								return $details;
							}
						}
					}
				}
			}
		}
				
		if($st->errno) {
			$this->error = $st->error;
		}

		return false;
	}

	/**
	 * Get a nodes from a nodegroup
	 * @param string $nodegroup
	 * @return mixed array of nodes (which may be empty) or false
	 */
	public function getNodesFromNodegroup($nodegroup) {
		$nodegroup = $this->stripAt($nodegroup);

		$query = 'SELECT `node` FROM `' . $this->prefix . 'nodes`';
		$query .= ' WHERE `nodegroup` = ?';

		$st = $this->mysql->prepare($query);
		if(!$st) {
			$this->error = $this->mysql->error;
			return false;
		}

		if($st->bind_param('s', &$nodegroup)) {
			if($st->execute()) {
				if($st->store_result()) {
					$result = $st->result_metadata();
					if($result) {
						if($st->bind_result($node)) {
							$nodes = array();
							while($st->fetch()) {
								$nodes[] = $node;
							}

							return $nodes;
						}
					}
				}
			}
		}
				
		if($st->errno) {
			$this->error = $st->error;
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
			$query_add = 'INSERT IGNORE INTO `' . $this->prefix . 'parent_child` (`parent`, `child`) VALUES ';
			$query_add .= implode(', ', $add_questions);

			$st = $this->mysql->prepare($query_add);
			if(!$st) {
				$this->error = $this->mysql->error;
				return false;
			}

			array_unshift($add, $binds . $binds);

			if(call_user_func_array(array($st, 'bind_param'), $add)) {
				if($st->execute()) {
					if($st->affected_rows < 1) {
						$this->error = 'Did not add any children';
						return false;
					}
				}
			}

			if($st->errno) {
				$this->error = $st->error;
				return false;
			}
		}

		$query_delete = 'DELETE FROM `' . $this->prefix . 'parent_child` WHERE ';
		$query_delete .= '`parent` = ?';

		if(!empty($save)) {
			$query_delete .= ' AND `child` NOT IN ';
			$query_delete .= '(' . implode(',', $save_questions) . ')';
		}

		$st = $this->mysql->prepare($query_delete);
		if(!$st) {
			$this->error = $this->mysql->error;
			return false;
		}

		array_unshift($save, $binds . 's', &$nodegroup);

		if(call_user_func_array(array($st, 'bind_param'), $save)) {
			if($st->execute()) {
				if($st->affected_rows >= 0) {
					return true;
				}
			}
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
			$query_add = 'INSERT IGNORE INTO `' . $this->prefix . 'nodes` (`nodegroup`, `node`) VALUES ';
			$query_add .= implode(', ', $add_questions);

			$st = $this->mysql->prepare($query_add);
			if(!$st) {
				$this->error = $this->mysql->error;
				return false;
			}

			array_unshift($add, $binds . $binds);

			if(call_user_func_array(array($st, 'bind_param'), $add)) {
				if($st->execute()) {
					if($st->affected_rows < 1) {
						$this->error = 'Did not add any nodes';
						return false;
					}
				}
			}

			if($st->errno) {
				$this->error = $st->error;
				return false;
			}
		}

		$query_delete = 'DELETE FROM `' . $this->prefix . 'nodes` WHERE ';
		$query_delete .= '`nodegroup` = ?';

		if(!empty($save)) {
			$query_delete .= ' AND `node` NOT IN ';
			$query_delete .= '(' . implode(',', $save_questions) . ')';
		}

		$st = $this->mysql->prepare($query_delete);
		if(!$st) {
			$this->error = $this->mysql->error;
			return false;
		}

		array_unshift($save, $binds . 's', &$nodegroup);

		if(call_user_func_array(array($st, 'bind_param'), $save)) {
			if($st->execute()) {
				if($st->affected_rows >= 0) {
					return true;
				}
			}
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
