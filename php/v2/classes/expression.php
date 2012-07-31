<?php

class NodegroupsAPIV2Expression {

	/**
	 * Parse expression
	 * @param string $expression
	 * @param bool $use_cache false to recursively parse
	 * @return array [inherited] nodes, [inherited] nodegroups
	 */
	public function parseExpression($expr = '', $use_cache = true) {
		global $drivers;
		$d_n = $drivers['v2_nodes'];
		$d_ng = $drivers['v2_nodegroups'];

		$expr = $this->sanitizeExpression($expr);

		$direct = array();
		$excluded = array();
		$i_nodegroups = array();
		$i_nodes = array();
		$nodegroups = array();
		$nodes = array();

		foreach($this->tokenizeExpression($expr) as $entity) {
			if(empty($entity)) {
				continue;
			}

			$negate = false;
			$parsed = array(
				'inherited_nodegroups' => array(),
				'inherited_nodes' => array(),
				'nodegroups' => array(),
				'nodes' => array(),
			);

			switch(substr($entity, 0, 1)) {
			case '#':
				continue 2;
			case '!':
				$entity = ltrim($entity, '!');
				$negate = true;
				break;
			}

			switch(substr($entity, 0, 1)) {
			case '&':
				// Entity is a function

				$parsed = $this->parseFunction($entity,
					$use_cache);

				$direct = array_merge($direct,
					array_diff($parsed['nodes'],
						$parsed['inherited_nodes']));

				break;
			case '@':
				// Entity is a nodegroup

				if($use_cache) {
					$temp = $d_n->getNodesFromNodegroup(
						$entity);

					$parsed['nodes'] = $temp['nodes'];
					$parsed['inherited_nodes'] =
						$temp['inherited'];

					// TODO: Determine inherited nodegroups
				} else {
					$temp = $d_ng->getNodegroupByID(
						$entity);

					$parsed = $this->parseExpression(
						$temp['expression'],
						$use_cache);
				}

				$parsed['nodegroups'][] = substr($entity, 1);

				$parsed['inherited_nodes'] = array_merge(
					$parsed['inherited_nodes'],
					$parsed['nodes']);

				break;
			default:
				$direct[] = $entity;
				$parsed['nodes'][] = $entity;
			}

			if($negate) {
				$excluded = array_merge($excluded,
					$parsed['nodes']);
			} else {
				$i_nodes = array_merge($i_nodes,
					$parsed['inherited_nodes']);
				$nodes = array_merge($nodes, $parsed['nodes']);
			}

			$i_nodegroups = array_merge($i_nodegroups,
				$parsed['inherited_nodegroups']);
			$nodegroups = array_merge($nodegroups,
				$parsed['nodegroups']);
		}

		$nodes = array_diff($nodes, $excluded);
		$i_nodes = array_diff($i_nodes, $direct, $excluded);

		// See the comments at
		// http://php.net/manual/en/function.array-unique.php
		// as to why this is faster than array_unique()
		$i_nodegroups =
			array_merge(array_flip(array_flip($i_nodegroups)));
		$i_nodes = array_merge(array_flip(array_flip($i_nodes)));
		$nodegroups = array_merge(array_flip(array_flip($nodegroups)));
		$nodes = array_merge(array_flip(array_flip($nodes)));

		natsort($i_nodegroups);
		natsort($i_nodes);
		natsort($nodegroups);
		natsort($nodes);

		// These next few lines re-index the array because
		// natsort() does not.

		$i_nodegroups = array_values($i_nodegroups);
		$i_nodes = array_values($i_nodes);
		$nodegroups = array_values($nodegroups);
		$nodes = array_values($nodes);

		return array(
			'inherited_nodegroups' => $i_nodegroups,
			'inherited_nodes' => $i_nodes,
			'nodegroups' => $nodegroups,
			'nodes' => $nodes,
		);
	}

	/**
	 * Parse a function
	 * @param string $expression
	 * @param bool False to parse recursively
	 * @return array nodes, nodegroups
	 */
	protected function parseFunction($expr, $use_cache) {
		$call_function = 'array_merge';
		$direct = array();
		$excluded = array();
		$i_nodegroups = array();
		$i_nodes = array();
		$is_regex = false;
		$nodegroups = array();
		$nodes = array();
		$values = array();

		list($function, $expr) = explode('(', $expr, 2);

		$expr = rtrim($expr, ')');

		if(substr($function, 0, 6) == '&regex') {
			$is_regex = true;
		}

		foreach($this->tokenizeExpression($expr) as $pos => $entity) {
			if(empty($entity)) {
				continue;
			}

			$negate = false;
			$parsed = array(
				'nodegroups' => array(),
				'nodes' => array(),
			);

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
				// The first entry of &regex() is the regex
				// and does not need to be parsed.
					$values[$entity] = $entity;
					continue;
				}
			}

			$parsed = $this->parseExpression($entity, $use_cache);

			$nodegroups = array_merge($nodegroups,
				$parsed['nodegroups']);

			$i_nodegroups = array_merge($i_nodegroups,
				$parsed['inherited_nodegroups']);

			if($negate) {
				$excluded = array_merge($excluded,
					$parsed['nodes']);
			} else {
				$values[$entity] = $parsed['nodes'];

				$i_nodes = array_merge($i_nodes,
					$parsed['inherited_nodes']);

				$direct = array_merge($direct, array_diff(
					$parsed['nodes'],
					$parsed['inherited_nodes']));
			}
		}

		switch($function) {
		case '&diff':
			$call_function = 'array_diff';
			break;
		case '&intersect':
			$call_function = 'array_intersect';
			break;
		case '&regex':
		case '&regexp':
			$call_function = array($this, 'parseFunction_regexp');
			break;
		default:
			$call_function = 'array_merge';
			break;
		}

		$nodes = call_user_func_array($call_function,
			array_values($values));

		$nodes = array_diff($nodes, $excluded);

		$i_nodegroups = array_intersect($i_nodegroups, $nodegroups);
		$i_nodes = array_intersect($i_nodes, $nodes);

		$i_nodes = array_diff($i_nodes, $direct);

		return array(
			'inherited_nodegroups' => $i_nodegroups,
			'inherited_nodes' => $i_nodes,
			'nodegroups' => $nodegroups,
			'nodes' => $nodes,
		);
	}

	/**
	 * Parse a regexp function
	 * @param array Function elements
	 * @return array Nodes (which may be empty)
	 */
	protected function parseFunction_regexp() {
		$input = func_get_args();
		$nodes = array();

		$regexp = array_shift($input);

		// $input is an array of arrays, e.g.,
		// array_diff($input[0], $input[1])
		$input = call_user_func_array('array_merge',
			array_values($input));

		while(list($junk, $node) = each($input)) {
			if(preg_match($regexp, $node)) {
				$nodes[] = $node;
			}
		}
		reset($input);

		return $nodes;
	}

	/**
	 * Sanitize expression
	 * @param string $expression
	 * @return string
	 */
	public function sanitizeExpression($expr) {
		$expr = str_replace("\n", ',', $expr);
		$expr = preg_replace('/\s+/', ',', $expr);
		$expr = preg_replace('/,,+/', ',', $expr);
		$expr = trim($expr, ',');

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
	 * @param bool
	 */
	public function validateExpression($input) {
		$status = false;

		if($input === '') {
		// Blank expressions are valid
			return true;
		}

		if(strpos($input, '&regex') !== false) {
		// TODO: Still validate the rest of the expression
			return true;
		}

		$input = $this->sanitizeExpression($input);

		// Test for only valid characters
		if(preg_match('/^[-\w\.\:\(\)\&\@\,\!\#]+$/i', $input)) {
			$status = true;
		} else {
			return false;
		}

		// Unbalanced parens and parens without functions
		// or functions without parens

		$ampersands = substr_count($input, '&');
		$open_parens = substr_count($input, '(');
		$close_parens = substr_count($input, ')');

		if($open_parens === $close_parens) {
			$status = true;
		} else {
			return false;
		}

		if($ampersands == $open_parens) {
			$status = true;
		} else {
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
					$status = true;
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
					$status = true;
					break;
				default:
					return false;
				}
			}
		}

		return $status;
	}
}

?>
