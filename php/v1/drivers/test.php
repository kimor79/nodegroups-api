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

	private $children = array();
	protected $error = '';
	private $nodegroups = array();
	private $nodes = array();
	public $slave_okay = false;

	public function __construct() {
	}

	/**
	 * Add a nodegroup
	 * @param string $nodegroup
	 * @param array $details
	 * @return bool
	 */
	public function addNodegroup($nodegroup = '', $details = array()) {
		$this->nodegroups[$nodegroup] = $details;

		return true;
	}

	/**
	 * Delete a nodegroup
	 * @param string $nodegroup
	 * @return bool
	 */
	public function deleteNodegroup($nodegroup) {
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
	 * @return mixed array with details or false
	 */
	public function getNodegroup($nodegroup) {
		if(array_key_exists($nodegroup, $this->nodegroups)) {
			return $this->nodegroups[$nodegroup];
		}

		$this->error = 'No such nodegroup';
		return false;
	}

	/**
	 * Set the list of nodegroups included in a nodegroup
	 * @param string $nodegroup
	 * @param array $children
	 * @return bool
	 */
	public function setChildren($nodegroup = '', $children = array()) {
		$this->children[$nodegroup] = $children;
	}

	/**
	 * Set nodes for given nodegroup
	 * @param string $nodegroup
	 * @param array $nodes
	 * @return bool
	 */
	public function setNodes($nodegroup = '', $nodes = array()) {
		$this->nodes[$nodegroup] = $nodes;
	}
}

?>
