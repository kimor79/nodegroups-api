<?php

include 'api_producer/v2/classes/input.php';

class NodegroupsAPIV2Input extends APIProducerV2Input {

	public function __construct() {
		parent::__construct();
	}

	public function __deconstruct() {
		parent::__deconstruct();
	}

	protected function sanitizeInput_expression($input) {
		global $ngexpr;
		return $ngexpr->sanitizeExpression($input);
	}

	protected function sanitizeInput_nodegroup_name($input) {
		return $this->sanitizeInput_tolower($input);
	}

	protected function validateInput_app($input) {
		if(strlen($input) <= 1024) {
			return true;
		}

		return false;
	}

	protected function validateInput_expression($input) {
		global $ngexpr;
		return $ngexpr->validateExpression($input);
	}

	protected function validateInput_nodegroup_name($input) {
		if(preg_match('/^@?[a-z0-9.:_-]+$/i', $input)) {
			return true;
		}

		return false;
	}
}

?>
