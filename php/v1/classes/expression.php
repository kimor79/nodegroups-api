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

class NodegroupsApiExpression {

	public function __construct() {
	}

	/**
	 * Parse expression
	 * @param string $expression
	 * @param bool $use_cache false to recursively parse
	 * @return array nodes => array(), children => array()
	 */
	public function parseExpression($expr = '', $use_cache = true) {
		global $driver;

		$entities = array();
		$entities_exclude = array();
		$nodegroups = array();

		$expr = $this->sanitizeExpression($expr);

		foreach($this->tokenizeExpression($expr) as $entity) {
			if(empty($entity)) {
				continue;
			}

			$negate = false;

			switch(substr($entity, 0, 1)) {
				case '#':
					continue 2;
				case '!':
					$entity = ltrim($entity, '!');
					$negate = true;
					break;
			}

			$first = substr($entity, 0, 1);
			if($first == '&') {
				$parsed = $this->parseFunction($entity,
					$use_cache);

				$list = $parsed['nodes'];
				$nodegroups = array_merge($nodegroups,
					$parsed['nodegroups']);
			} elseif($first == '@') {
				$nodegroups[$entity] = substr($entity, 1);

				if($use_cache) {
					$list = $driver->getNodesFromNodegroup(
						array($entity));
				} else {
					$details =
						$driver->getNodegroup($entity);
					$parsed = $this->parseExpression(
						$details['expression'],
						$use_cache);

					$list = $parsed['nodes'];
				}
			} else {
				$list = array($entity);
			}

			if($negate) {
				$entities_exclude = array_merge(
					$entities_exclude, $list);
			} else {
				$entities = array_merge($entities, $list);
			}
		}

		$nodes = array_diff($entities, $entities_exclude);

		// See the comments at
		// http://php.net/manual/en/function.array-unique.php
		// as to why this is faster than array_unique()
		$nodegroups = array_merge(array_flip(array_flip($nodegroups)));
		$nodes = array_merge(array_flip(array_flip($nodes)));

		return array(
			'nodegroups' => $nodegroups,
			'nodes' => $nodes,
		);
	}

	/**
	 * Parse a function
	 * @param string $input
	 * @param bool $use_cache
	 * @return array
	 */
	protected function parseFunction($input, $use_cache = true) {
		$call_function = 'array_merge';
		$exclude = array();
		$include = array();
		$is_regex = false;
		$nodegroups = array();
		$nodes = array();

		list($function, $expr) = explode('(', $input, 2);

		if(substr($expr, -1, 1) == ')') {
			$expr = substr($expr, 0, -1);
		}

		if(substr($function, 0, 6) == '&regex') {
			$is_regex = true;
		}

		foreach($this->tokenizeExpression($expr) as $pos => $entity) {
			if(empty($entity)) {
				continue;
			}

			$negate = false;

			switch(substr($entity, 0, 1)) {
				case '#':
					continue 2;
				case '!':
					$entity = ltrim($entity, '!');
					$negate = true;
					break;
			}

			if($is_regex) {
				if($pos === 0) {
				// The first entity of &regexp() is the regex
				// and does not need to be parsed.
					$include[$entity] = $entity;
					continue;
				}
			}

			$parsed = $this->parseExpression($entity, $use_cache);

			$nodegroups = array_merge($nodegroups,
				$parsed['nodegroups']);

			if($negate) {
				$exclude = array_merge($exclude,
					$parsed['nodes']);
			} else {
				$include[$entity] = $parsed['nodes'];
			}
		}

		switch($function) {
			case '&diff':
				$call_function = 'array_diff';
				break;
			case '&regex':
			case '&regexp':
				$call_function = array($this,
					'parseFunction_regexp');
				break;
			case '&intersect':
				$call_function = 'array_intersect';
				break;
			case '&union':
				$call_function = 'array_merge';
				break;
		}

		$nodes = call_user_func_array($call_function,
			array_values($include));

		$nodes = array_diff($nodes, $exclude);

		return array(
			'nodegroups' => array_values($nodegroups),
			'nodes' => $nodes,
		);
	}

	/**
	 * Parse a regex
	 * @param array $input
	 * @return array
	 */
	protected function parseFunction_regexp() {
		$array = func_get_args();
		$regexp = array_shift($array);
		$array = call_user_func_array('array_merge',
			array_values($array));

		return array_filter($array, array(new
			NodegroupsApiExpressionRegexp($regexp),
			'functionRegexp'));
	}

	/**
	 * Sanitize expression
	 * @param string $expression
	 * @return string $expression
	 */
	protected function sanitizeExpression($expr) {
		$expr = str_replace("\n", ',', $expr);
		$expr = str_replace(', ', ',', $expr);
		$expr = str_replace(',,', ',', $expr);
		$expr = str_replace('( ', '(', $expr);
		$expr = str_replace(' )', ')', $expr);
		$expr = trim($expr, ',');
		$expr = trim($expr);

		return $expr;
	}

	/**
	 * Tokenize expression
	 * @param string $expression
	 * @return array tokens
	*/
	protected function tokenizeExpression($expr) {
		$tokens = array();

		$chars = str_split($expr);
		$length = strlen($expr) - 1;

		$is_function = false;
		$is_regex = false;
		$nested_function = 0;
		$reset = false;
		$token = '';

		foreach($chars as $pos => $char) {
			if($char === '/') {
				if($is_regex) {
					if($chars[$pos - 1] != '\\') {
						$is_regex = false;
					}
				} else {
					$is_regex = true;
				}
			}

			if($is_regex) {
				$token .= $char;
				continue;
			}

			switch($char) {
				case '&':
					$token .= $char;

					if($is_function) {
						$nested_function++;
					}

					$is_function = true;

					break;
				case ')':
					$token .= $char;

					if($nested_function > 0) {
						$nested_function--;
					} else {
						$reset = true;
					}

					break;
				case ',':
					if($is_function) {
						$token .= $char;
					} else {
						$reset = true;
					}

					break;
				default:
					$token .= $char;
			}

			if(($reset) || $pos === $length ) {
				$tokens[] = $token;

				$is_function = false;
				$is_regex = false;
				$nested_function = 0;
				$reset = false;
				$token = '';
			}
		}

		return $tokens;
	}

	/**
	 * Validate expression
	 * Will return true if &regex is used
	 * @param string $input
	 * @param array list of errors (if any)
	 */
	public function validateExpression($input) {
		if($input === '') {
		// Blank expressions are valid
			return true;
		}

		if(strpos($input, '&regex') !== false) {
		// TODO: figure out how to still validate
		// the rest of the expression
			return true;
		}

		$input = $this->sanitizeExpression($input);

		// Test for only valid characters
		if(!preg_match('/^[-\w\.\(\)\&\@\,\!\#]+$/i', $input)) {
			return false;
		}

		// Unbalanced parens and parens
		// without functions (or visa versa)
		$ampersands = substr_count($input, '&');
		$open_parens = substr_count($input, '(');
		$close_parens = substr_count($input, ')');

		if($open_parens !== $close_parens) {
			return false;
		}

		if($ampersands !== $open_parens) {
			return false;
		}

		// Test for only valid function names
		if(strpos($input, '&') !== false) {
			$function_total = preg_match_all('/\&[^\(]*\(/',
				$input, $functions);
			if($function_total == 0) {
				return false;
			}

			foreach($functions[0] as $function) {
				switch($function) {
					case '&diff(':
					case '&intersect(':
					case '&regex(':
					case '&regexp(':
					case '&union(':
						break;
					default:
						return false;
				}
			}
		}

		// Test for only valid meta character sequences
		if(preg_match_all('/[^-\w\.][^-\w\.]/',
				$input, $double_matches)) {
			foreach($double_matches[0] as $double) {
				switch($double) {
					case ',#':
					case ',!':
					case ',&':
					case ',@':
					case '!&':
					case '!@':
					case '#!':
					case '#&':
					case '#@':
					case '(#':
					case '(!':
					case '(&':
					case '(@':
					case '),':
					case '))':
						break;
					default:
						return false;
				}
			}
		}

		return true;
	}
}

class NodegroupsApiExpressionRegexp {
	private $regexp = '';

	public function __construct($regexp) {
		$this->regexp = $regexp;
	}

	public function functionRegexp($node = false) {
		return preg_match($this->regexp, $node);
	}
}

?>
