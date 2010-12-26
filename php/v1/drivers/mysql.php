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
	private $link;
	private $prefix = '';
	public $slave_okay = false;

	public function __construct() {
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

		$this->link = mysql_connect($host, $user, $password);

		if(!$this->link) {
			throw new Exception(mysql_error());
		}

		if(!mysql_select_db($database)) {
			throw new Exception(mysql_error());
		}
	}

	/**
	 * Add a nodegroup
	 * @param string $nodegroup
	 * @param array $details
	 * @return bool
	 */
	public function addNodegroup($nodegroup = '', $details = array()) {

		return false;
	}

	/**
	 * Delete a nodegroup
	 * @param string $nodegroup
	 * @return bool
	 */
	public function deleteNodegroup($nodegroup) {

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
	 * @return mixed array with details or false
	 */
	public function getNodegroup($nodegroup) {

		return false;
	}

	/**
	 * Set the list of nodegroups included in a nodegroup
	 * @param string $nodegroup
	 * @param array $children
	 * @return bool
	 */
	public function setChildren($nodegroup = '', $children = array()) {

		return false;
	}

	/**
	 * Set nodes for given nodegroup
	 * @param string $nodegroup
	 * @param array $nodes
	 * @return bool
	 */
	public function setNodes($nodegroup = '', $nodes = array()) {

		return false;
	}
}

?>