<?php

class NodegroupsAPIV2DriverNodegroupsTest {

	protected $nodegroups = array(
		'a' => array(
			'description' => '',
			'expression' => 'a,b,c,d,e,f,g',
			'nodegroup' => 'a',
		),

		'b' => array(
			'description' => '',
			'expression' => 'h,i,j,k,l,m,n',
			'nodegropup' => 'b',
		),

		'c' => array(
			'description' => '',
			'expression' => '1,2,3,4,5,6,7',
			'nodegroup' => 'c',
		),

		'd' => array(
			'description' => '',
			'expression' => 'a,h,1,b,i,2,z',
			'nodegroup' => 'd',
		),

		'e' => array(
			'description' => '',
			'expression' => 'c,3,t,u,v,w,x',
			'nodegroup' => 'e',
		),

		'f' => array(
			'description' => '',
			'expression' => 'a,h,1,b,v,w,x',
			'nodegroup' => 'f',
		),

		'g' => array(
			'description' => '',
			'expression' =>
			'a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z',
			'nodegroup' => 'g',
		),

		'h' => array(
			'description' => '',
			'expression' => 'a,b,c,d,e,f,h,i,j',
			'nodegroup' => 'h',
		),

		'i' => array(
			'description' => '',
			'expression' => '@a, @b',
			'nodegroup' => 'i',
		),

		'j' => array(
			'description' => '',
			'expression' => 'a, b, @a',
			'nodegroup' => 'j',
		),
	);

	protected $nodes = array(
		'a' => array(
			'inherited' => array(),
			'nodes' => array('a', 'b', 'c', 'd', 'e', 'f', 'g'),
		),

		'b' => array(
			'inherited' => array(),
			'nodes' => array('h', 'i', 'j', 'k', 'l', 'm', 'n'),
		),

		'c' => array(
			'inherited' => array(),
			'nodes' => array('1', '2', '3', '4', '5', '6', '7'),
		),

		'd' => array(
			'inherited' => array(),
			'nodes' => array('1', '2', 'a', 'b', 'h', 'i', 'z'),
		),

		'e' => array(
			'inherited' => array(),
			'nodes' => array('3', 'c', 't', 'u', 'v', 'w', 'x'),
		),

		'f' => array(
			'inherited' => array(),
			'nodes' => array('1', 'a', 'b', 'h', 'v', 'w', 'x'),
		),

		'g' => array(
			'inherited' => array(),
			'nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'f', 'g',
				'h', 'i', 'j', 'k', 'l', 'm', 'n',
				'o', 'p', 'q', 'r', 's', 't', 'u',
				'v', 'w', 'x', 'y', 'z',
			),
		),

		'h' => array(
			'inherited' => array(),
			'nodes' => array(
				'a', 'b', 'c', 'd', 'e',
				'f', 'h', 'i', 'j',
			),
		),

		'i' => array(
			'inherited' => array(
				'a', 'b', 'c', 'd', 'e', 'f', 'g',
				'h', 'i', 'j', 'k', 'l', 'm', 'n',
			),
			'nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'f', 'g',
				'h', 'i', 'j', 'k', 'l', 'm', 'n',
			),
		),

		'j' => array(
			'inherited' => array(
				'c', 'd', 'e', 'f', 'g',
			),
			'nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'f', 'g',
			),
		),
	);

	public function getNodegroupByID($nodegroup) {
		$nodegroup = $this->stripAt($nodegroup);

		if(array_key_exists($nodegroup, $this->nodegroups)) {
			return $this->nodegroups[$nodegroup];
		}

		return array();
	}

	public function getNodesFromNodegroup($nodegroup) {
		$nodegroup = $this->stripAt($nodegroup);

		if(array_key_exists($nodegroup, $this->nodes)) {
			return $this->nodes[$nodegroup];
		}

		return array();
	}

	protected function stripAt($nodegroup) {
		if(substr($nodegroup, 0, 1) == '@') {
			$nodegroup = substr($nodegroup, 1);
		}

		return $nodegroup;
	}
}

?>
