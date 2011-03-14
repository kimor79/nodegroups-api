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

class NodegroupsApiDriverTestInMemory {

	protected $children = array();
	protected $count = 0;
	protected $error = '';
	protected $nodegroups = array();
	protected $nodes = array();
	protected $slave_okay = false;

	public function __construct($slave_okay = false) {
	}

	public function __deconstruct() {
	}

	/**
	 * Add a nodegroup
	 * @param string $nodegroup
	 * @param array $details
	 * @return bool
	 */
	public function addNodegroup($nodegroup = '', $details = array()) {
		$nodegroup = $this->stripAt($nodegroup);

		$this->nodegroups[$nodegroup] = $details;
		$this->nodegroups[$nodegroup]['nodegroup'] = $nodegroup;

		return true;
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

		unset($this->nodegroups[$nodegroup]);
		return true;
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

		if(array_key_exists($nodegroup, $this->nodegroups)) {
			return $this->nodegroups[$nodegroup];
		}

		return false;
	}

	/**
	 * List nodegroups for a node
	 * @param array $nodes array('re' => array(), 'eq' => array())
	 * @param string $app
	 * @param array $options sort col, start, end
	 * @return mixed array of nodes (which may be empty) or false
	 */
	public function listNodegroupsFromNode($input,
			$app = '', $options = array()) {

		$this->error = 'Not Implemented';
		return false;
	}

	/**
	 * Listnodes from a nodegroup
	 * @param array $nodegroups
	 * @param array $options sort col, start, end
	 * @return mixed array of nodes (which may be empty) or false
	 */
	public function listNodesFromNodegroup($input, $options = array()) {
		$nodes = array();

		while(list($key, $group) = each($input)) {
			$t_group = $this->stripAt($group);

			if(array_key_exists($t_group, $this->nodes)) {
				$nodes = array_merge($nodes,
					$this->nodes[$t_group]);
			}
		}

		return $nodes;
	}

	/**
	 * Set the list of nodegroups included in a nodegroup
	 * @param string $nodegroup
	 * @param array $children
	 * @return bool
	 */
	public function setChildren($nodegroup = '', $children = array()) {
		$nodegroup = $this->stripAt($nodegroup);

		$this->children[$nodegroup] = $children;

		return true;
	}

	/**
	 * Set nodes for given nodegroup
	 * @param string $nodegroup
	 * @param array $nodes
	 * @return bool
	 */
	public function setNodes($nodegroup = '', $nodes = array()) {
		$nodegroup = $this->stripAt($nodegroup);

		$this->nodes[$nodegroup] = $nodes;

		return true;
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
