<?php

require_once __DIR__ . '/driver-nodegroups.php';

$drivers = array();
$drivers['v2_nodegroups'] = new NodegroupsAPIV2DriverNodegroupsTest();
$drivers['v2_nodes'] = $drivers['v2_nodegroups'];

require_once __DIR__ . '/../../../php/v2/classes/expression.php';

class NodegroupsAPIV2ExpressionTest extends PHPUnit_Framework_TestCase {

	protected $parser;

	protected function setUP() {
		$this->parser = new NodegroupsAPIV2Expression();
	}

	public function testParseExpression() {
		$expr = 'foo, bar';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('bar', 'foo'),
			'nodegroups' => array(),
			'inherited_nodes' => array(),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression1() {
		$expr = 'foo, bar, !bar';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('foo'),
			'nodegroups' => array(),
			'inherited_nodes' => array(),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression2() {
		$expr = 'foo, bar, #bar';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('bar', 'foo'),
			'nodegroups' => array(),
			'inherited_nodes' => array(),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression3() {
		$expr = 'foo, !bar, bar';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('foo'),
			'nodegroups' => array(),
			'inherited_nodes' => array(),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression4() {
		$expr = 'foo, #bar, bar';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('bar', 'foo'),
			'nodegroups' => array(),
			'inherited_nodes' => array(),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression5() {
		$expr = 'foo, #bar, !bar';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('foo'),
			'nodegroups' => array(),
			'inherited_nodes' => array(),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression6() {
		$expr = '@a';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('a', 'b', 'c', 'd', 'e', 'f', 'g'),
			'nodegroups' => array('a'),
			'inherited_nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'f', 'g',
			),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression7() {
		$expr = '@a, @b';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'f', 'g',
				'h', 'i', 'j', 'k', 'l', 'm', 'n',
			),
			'nodegroups' => array('a', 'b'),
			'inherited_nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'f', 'g',
				'h', 'i', 'j', 'k', 'l', 'm', 'n',
			),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression8() {
		$expr = '@a, @b, !f';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'g', 'h',
				'i', 'j', 'k', 'l', 'm', 'n',
			),
			'nodegroups' => array('a', 'b'),
			'inherited_nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'g', 'h',
				'i', 'j', 'k', 'l', 'm', 'n',
			),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression9() {
		$expr = '@a, @b, #!f';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'f', 'g',
				'h', 'i', 'j', 'k', 'l', 'm', 'n',
			),
			'nodegroups' => array('a', 'b'),
			'inherited_nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'f', 'g',
				'h', 'i', 'j', 'k', 'l', 'm', 'n',
			),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression10() {
		$expr = '@a, @b, f';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'f', 'g',
				'h', 'i', 'j', 'k', 'l', 'm', 'n',
			),
			'nodegroups' => array('a', 'b'),
			'inherited_nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'g', 'h',
				'i', 'j', 'k', 'l', 'm', 'n',
			),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression11() {
		$expr = '@a, !@b';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'f', 'g',
			),
			'nodegroups' => array('a', 'b'),
			'inherited_nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'f', 'g',
			),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression12() {
		$expr = '@a, !@d';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('c', 'd', 'e', 'f', 'g'),
			'nodegroups' => array('a', 'd'),
			'inherited_nodes' => array('c', 'd', 'e', 'f', 'g'),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression13() {
		$expr = '@g, !@a, !@b';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array(
				'o', 'p', 'q', 'r', 's', 't',
				'u', 'v', 'w', 'x', 'y', 'z',
			),
			'nodegroups' => array('a', 'b', 'g'),
			'inherited_nodes' => array(
				'o', 'p', 'q', 'r', 's', 't',
				'u', 'v', 'w', 'x', 'y', 'z',
			),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression14() {
		$expr = '@g, !@a, !@d';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array(
				'j', 'k', 'l', 'm', 'n', 'o',
				'p', 'q', 'r', 's', 't', 'u',
				'v', 'w', 'x', 'y',
			),
			'nodegroups' => array('a', 'd', 'g'),
			'inherited_nodes' => array(
				'j', 'k', 'l', 'm', 'n', 'o',
				'p', 'q', 'r', 's', 't', 'u',
				'v', 'w', 'x', 'y',
			),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression15() {
		$expr = '@g, !@a, !@h';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array(
				'k', 'l', 'm', 'n', 'o', 'p',
				'q', 'r', 's', 't', 'u', 'v',
				'w', 'x', 'y', 'z',
			),
			'nodegroups' => array('a', 'g', 'h'),
			'inherited_nodes' => array(
				'k', 'l', 'm', 'n', 'o', 'p',
				'q', 'r', 's', 't', 'u', 'v',
				'w', 'x', 'y', 'z',
			),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression16() {
		$expr = '@g, !@h, !@a';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array(
				'k', 'l', 'm', 'n', 'o', 'p',
				'q', 'r', 's', 't', 'u', 'v',
				'w', 'x', 'y', 'z',
			),
			'nodegroups' => array('a', 'g', 'h'),
			'inherited_nodes' => array(
				'k', 'l', 'm', 'n', 'o', 'p',
				'q', 'r', 's', 't', 'u', 'v',
				'w', 'x', 'y', 'z',
			),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression17() {
		$expr = '&intersect(@a, @b)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array(),
			'nodegroups' => array('a', 'b'),
			'inherited_nodes' => array(),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression18() {
		$expr = '&union(@a, @b)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'f', 'g',
				'h', 'i', 'j', 'k', 'l', 'm', 'n',
			),
			'nodegroups' => array('a', 'b'),
			'inherited_nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'f', 'g',
				'h', 'i', 'j', 'k', 'l', 'm', 'n',
			),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression19() {
		$expr = '&diff(@a, @b)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'f', 'g',
			),
			'nodegroups' => array('a', 'b'),
			'inherited_nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'f', 'g',
			),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression20() {
		$expr = '&intersect(@a, @d)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('a', 'b'),
			'nodegroups' => array('a', 'd'),
			'inherited_nodes' => array('a', 'b'),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression22() {
		$expr = '&diff(@a, @d)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('c', 'd', 'e', 'f', 'g'),
			'nodegroups' => array('a', 'd'),
			'inherited_nodes' => array('c', 'd', 'e', 'f', 'g'),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression23() {
		$expr = '&union(@a, @b)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'f', 'g',
				'h', 'i', 'j', 'k', 'l', 'm', 'n',
			),
			'nodegroups' => array('a', 'b'),
			'inherited_nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'f', 'g',
				'h', 'i', 'j', 'k', 'l', 'm', 'n',
			),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression24() {
		$expr = '&intersect(@a, @d, @e)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array(),
			'nodegroups' => array('a', 'd', 'e'),
			'inherited_nodes' => array(),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression25() {
		$expr = '&intersect(@a, @d, @f)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('a', 'b'),
			'nodegroups' => array('a', 'd', 'f'),
			'inherited_nodes' => array('a', 'b'),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression26() {
		$expr = '&union(@a, @d, @e)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array(
				'1', '2', '3', 'a', 'b', 'c',
				'd', 'e', 'f', 'g', 'h', 'i',
				't', 'u', 'v', 'w', 'x', 'z'
			),
			'nodegroups' => array('a', 'd', 'e'),
			'inherited_nodes' => array(
				'1', '2', '3', 'a', 'b', 'c',
				'd', 'e', 'f', 'g', 'h', 'i',
				't', 'u', 'v', 'w', 'x', 'z'
			),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression27() {
		$expr = '&diff(@a, @d, @e)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('d', 'e', 'f', 'g'),
			'nodegroups' => array('a', 'd', 'e'),
			'inherited_nodes' => array('d', 'e', 'f', 'g'),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression28() {
		$expr = '&union(@a, !c)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array(
				'a', 'b', 'd', 'e', 'f', 'g',
			),
			'nodegroups' => array('a'),
			'inherited_nodes' => array(
				'a', 'b', 'd', 'e', 'f', 'g',
			),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression29() {
		$expr = '&union(@a, #!c)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'f', 'g',
			),
			'nodegroups' => array('a'),
			'inherited_nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'f', 'g',
			),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression30() {
		$expr = '&union(@a, c)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'f', 'g',
			),
			'nodegroups' => array('a'),
			'inherited_nodes' => array(
				'a', 'b', 'd', 'e', 'f', 'g',
			),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression31() {
		$expr = '&intersect(@a, c)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('c'),
			'nodegroups' => array('a'),
			'inherited_nodes' => array(),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression32() {
		$expr = '&diff(@a, c)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array(
				'a', 'b', 'd', 'e', 'f', 'g',
			),
			'nodegroups' => array('a'),
			'inherited_nodes' => array(
				'a', 'b', 'd', 'e', 'f', 'g',
			),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression33() {
		$expr = '&intersect(@a, @d), foo';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('a', 'b', 'foo'),
			'nodegroups' => array('a', 'd'),
			'inherited_nodes' => array('a', 'b'),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression34() {
		$expr = '&intersect(@a, @d), !b';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('a'),
			'nodegroups' => array('a', 'd'),
			'inherited_nodes' => array('a'),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression35() {
		$expr = '&intersect(@a, @d), #b';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('a', 'b'),
			'nodegroups' => array('a', 'd'),
			'inherited_nodes' => array('a', 'b'),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression36() {
		$expr = '&intersect(@a, @d), #!b';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('a', 'b'),
			'nodegroups' => array('a', 'd'),
			'inherited_nodes' => array('a', 'b'),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression37() {
		$expr = '&intersect(@a, @d), #@d';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('a', 'b'),
			'nodegroups' => array('a', 'd'),
			'inherited_nodes' => array('a', 'b'),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression38() {
		$expr = '&intersect(@a, @d), @d';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array(
				'1', '2', 'a', 'b', 'h', 'i', 'z',
			),
			'nodegroups' => array('a', 'd'),
			'inherited_nodes' => array(
				'1', '2', 'a', 'b', 'h', 'i', 'z',
			),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression39() {
		$expr = 'foo, &intersect(@a, @d)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('a', 'b', 'foo'),
			'nodegroups' => array('a', 'd'),
			'inherited_nodes' => array('a', 'b'),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression40() {
		$expr = '!b, &intersect(@a, @d)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('a'),
			'nodegroups' => array('a', 'd'),
			'inherited_nodes' => array('a'),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression41() {
		$expr = '#b, &intersect(@a, @d)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('a', 'b'),
			'nodegroups' => array('a', 'd'),
			'inherited_nodes' => array('a', 'b'),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression42() {
		$expr = '#!b, &intersect(@a, @d)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('a', 'b'),
			'nodegroups' => array('a', 'd'),
			'inherited_nodes' => array('a', 'b'),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression43() {
		$expr = 'b, &intersect(@a, @d)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('a', 'b'),
			'nodegroups' => array('a', 'd'),
			'inherited_nodes' => array('a'),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression44() {
		$expr = '#@d, &intersect(@a, @d)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('a', 'b'),
			'nodegroups' => array('a', 'd'),
			'inherited_nodes' => array('a', 'b'),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression45() {
		$expr = '@d, &intersect(@a, @d)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array(
				'1', '2', 'a', 'b', 'h', 'i', 'z',
			),
			'nodegroups' => array('a', 'd'),
			'inherited_nodes' => array(
				'1', '2', 'a', 'b', 'h', 'i', 'z',
			),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression46() {
		$expr = '@d, &intersect(@a, @d), b';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array(
				'1', '2', 'a', 'b', 'h', 'i', 'z',
			),
			'nodegroups' => array('a', 'd'),
			'inherited_nodes' => array(
				'1', '2', 'a', 'h', 'i', 'z',
			),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression47() {
		$expr = 'foo, &intersect(@a, @d), &diff(@a, @b)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'f', 'foo', 'g',
			),
			'nodegroups' => array('a', 'b', 'd'),
			'inherited_nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'f', 'g',
			),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression48() {
		$expr = '!b, &intersect(@a, @d), &diff(@a, @b)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array(
				'a', 'c', 'd', 'e', 'f', 'g',
			),
			'nodegroups' => array('a', 'b', 'd'),
			'inherited_nodes' => array(
				'a', 'c', 'd', 'e', 'f', 'g',
			),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression49() {
		$expr = '#b, &intersect(@a, @d), &diff(@a, @b)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'f', 'g',
			),
			'nodegroups' => array('a', 'b', 'd'),
			'inherited_nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'f', 'g',
			),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression50() {
		$expr = 'b, &intersect(@a, @d), &diff(@a, @b)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'f', 'g',
			),
			'nodegroups' => array('a', 'b', 'd'),
			'inherited_nodes' => array(
				'a', 'c', 'd', 'e', 'f', 'g',
			),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression51() {
		$expr = '#!b, &intersect(@a, @d), &diff(@a, @b)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'f', 'g',
			),
			'nodegroups' => array('a', 'b', 'd'),
			'inherited_nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'f', 'g',
			),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression52() {
		$expr = '#@d, &intersect(@a, @d), &diff(@a, @b)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'f', 'g',
			),
			'nodegroups' => array('a', 'b', 'd'),
			'inherited_nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'f', 'g',
			),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression53() {
		$expr = '@d, &intersect(@a, @d), &diff(@a, @b)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array(
				'1', '2', 'a', 'b', 'c', 'd',
				'e', 'f', 'g', 'h', 'i', 'z',
			),
			'nodegroups' => array('a', 'b', 'd'),
			'inherited_nodes' => array(
				'1', '2', 'a', 'b', 'c', 'd',
				'e', 'f', 'g', 'h', 'i', 'z',
			),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression54() {
		$expr = '&intersect(@a, @d), &diff(@a, @b)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'f', 'g',
			),
			'nodegroups' => array('a', 'b', 'd'),
			'inherited_nodes' => array(
				'a', 'b', 'c', 'd', 'e', 'f', 'g',
			),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression55() {
		$expr = '&regexp(/^a$/, @b)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array(),
			'nodegroups' => array('b'),
			'inherited_nodes' => array(),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression56() {
		$expr = '&regexp(/^foo$/, foo, bar)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('foo'),
			'nodegroups' => array(),
			'inherited_nodes' => array(),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression57() {
		$expr = '&regexp(/^foo$/, foo, bar, foobar)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('foo'),
			'nodegroups' => array(),
			'inherited_nodes' => array(),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression58() {
		$expr = '&regexp(/^foo/, foo, bar, foobar)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('foo', 'foobar'),
			'nodegroups' => array(),
			'inherited_nodes' => array(),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression59() {
		$expr ='&regexp(/^foo1\./, foo, bar, foobar, foo1bar, foo1.bar)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('foo1.bar'),
			'nodegroups' => array(),
			'inherited_nodes' => array(),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression60() {
		$expr ='&regexp(/^a$/, @a)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('a'),
			'nodegroups' => array('a'),
			'inherited_nodes' => array('a'),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression61() {
		$expr ='&regexp(/^a$/i, @a, @b)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('a'),
			'nodegroups' => array('a', 'b'),
			'inherited_nodes' => array('a'),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression62() {
		$expr ='&regexp(/^A$/, @a, @b)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array(),
			'nodegroups' => array('a', 'b'),
			'inherited_nodes' => array(),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression63() {
		$expr ='&regexp(/^A$/i, @a, @b)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('a'),
			'nodegroups' => array('a', 'b'),
			'inherited_nodes' => array('a'),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression64() {
		$expr ='&regexp(/^a$/, @a, @b)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('a'),
			'nodegroups' => array('a', 'b'),
			'inherited_nodes' => array('a'),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression65() {
		$expr ='&regexp(/fo(o|p)$/, foo, bar, fop)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('foo', 'fop'),
			'nodegroups' => array(),
			'inherited_nodes' => array(),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testParseExpression66() {
		$expr ='&regexp(/fo(o|p)$/, foo, bar, fom)';
		$got = $this->parser->parseExpression($expr);
		$expect = array(
			'nodes' => array('foo'),
			'nodegroups' => array(),
			'inherited_nodes' => array(),
			'inherited_nodegroups' => array(),
		);

		$this->assertEquals($expect, $got, $expr);
	}

	public function testValidateExpression() {
		$expr = '';
		$got = $this->parser->validateExpression($expr);

		$this->assertTrue($got, $expr);
	}

	public function testValidateExpression1() {
		$expr = 'a';
		$got = $this->parser->validateExpression($expr);

		$this->assertTrue($got, $expr);
	}

	public function testValidateExpression2() {
		$expr = 'a,b';
		$got = $this->parser->validateExpression($expr);

		$this->assertTrue($got, $expr);
	}

	public function testValidateExpression3() {
		$expr = 'a,@b';
		$got = $this->parser->validateExpression($expr);

		$this->assertTrue($got, $expr);
	}

	public function testValidateExpression4() {
		$expr = 'a,!@b';
		$got = $this->parser->validateExpression($expr);

		$this->assertTrue($got, $expr);
	}

	public function testValidateExpression5() {
		$expr = 'a,!@b, #c';
		$got = $this->parser->validateExpression($expr);

		$this->assertTrue($got, $expr);
	}

	public function testValidateExpression6() {
		$expr = 'a,!@b, #!c';
		$got = $this->parser->validateExpression($expr);

		$this->assertTrue($got, $expr);
	}

	public function testValidateExpression7() {
		$expr = 'a,!@b, !#c';
		$got = $this->parser->validateExpression($expr);

		// TODO: Fix
		$this->markTestIncomplete($expr);

		$this->assertFalse($got, $expr);
	}

	public function testValidateExpression8() {
		$expr = 'a
b
c';
		$got = $this->parser->validateExpression($expr);

		$this->assertTrue($got, $expr);
	}

	public function testValidateExpression9() {
		$expr = 'a
@b
c';
		$got = $this->parser->validateExpression($expr);

		$this->assertTrue($got, $expr);
	}

	public function testValidateExpression10() {
		$expr = '@';
		$got = $this->parser->validateExpression($expr);

		// TODO: Fix
		$this->markTestIncomplete($expr);

		$this->assertFalse($got, $expr);
	}

	public function testValidateExpression11() {
		$expr = 'a
b';
		$got = $this->parser->validateExpression($expr);

		$this->assertTrue($got, $expr);
	}

	public function testValidateExpression12() {
		$expr = '&intersect(a
b)';
		$got = $this->parser->validateExpression($expr);

		$this->assertTrue($got, $expr);
	}

	public function testValidateExpression13() {
		$expr = '&intersect(a
b
)';
		$got = $this->parser->validateExpression($expr);

		$this->assertTrue($got, $expr);
	}
}

?>
