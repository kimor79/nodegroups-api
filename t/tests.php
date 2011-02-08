<?php

$tests = array(
	array('expr' => 'foo, bar',
		'nodegroups' => array(),
		'nodes' => array('foo', 'bar'),
	),
	array('expr' => 'foo, bar, !bar',
		'nodegroups' => array(),
		'nodes' => array('foo'),
	),
	array('expr' => 'foo, bar, #bar',
		'nodegroups' => array(),
		'nodes' => array('foo', 'bar'),
	),

	array('expr' => 'foo, !bar, bar',
		'nodegroups' => array(),
		'nodes' => array('foo'),
	),
	array('expr' => 'foo, #bar, bar',
		'nodegroups' => array(),
		'nodes' => array('foo', 'bar'),
	),

	array('expr' => 'foo, #bar, !bar',
		'nodegroups' => array(),
		'nodes' => array('foo'),
	),

	array('expr' => '@a',
		'nodegroups' => array('a'),
		'nodes' => array('a', 'b', 'c', 'd', 'e', 'f', 'g'),
	),
	array('expr' => '@a, @b',
		'nodegroups' => array('a', 'b'),
		'nodes' => array(
			'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n'),
	),
	array('expr' => '@a, @b, !f',
		'nodegroups' => array('a', 'b'),
		'nodes' => array(
			'a', 'b', 'c', 'd', 'e', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n'),
	),
	array('expr' => '@a, @b, #!f',
		'nodegroups' => array('a', 'b'),
		'nodes' => array(
			'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n'),
	),

	array('expr' => '@a, !@b',
		'nodegroups' => array('a', 'b'),
		'nodes' => array('a', 'b', 'c', 'd', 'e', 'f', 'g'),
	),
	array('expr' => '@a, !@d',
		'nodegroups' => array('a', 'd'),
		'nodes' => array('c', 'd', 'e', 'f', 'g'),
	),
	array('expr' => '@g, !@a, !@b',
		'nodegroups' => array('a', 'b', 'g'),
		'nodes' => array('o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'),
	),
	array('expr' => '@g, !@a, !@d',
		'nodegroups' => array('a', 'd', 'g'),
		'nodes' => array('j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y'),
	),
	array('expr' => '@g, !@a, !@h',
		'nodegroups' => array('a', 'g', 'h'),
		'nodes' => array('k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'),
	),
	array('expr' => '@g, !@h, !@a',
		'nodegroups' => array('a', 'g', 'h'),
		'nodes' => array('k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'),
	),

	array('expr' => '&intersect(@a, @b)',
		'nodegroups' => array('a', 'b'),
		'nodes' => array(),
	),
	array('expr' => '&union(@a, @b)',
		'nodegroups' => array('a', 'b'),
		'nodes' => array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n'),
	),
	array('expr' => '&diff(@a, @b)',
		'nodegroups' => array('a', 'b'),
		'nodes' => array('a', 'b', 'c', 'd', 'e', 'f', 'g'),
	),

	array('expr' => '&intersect(@a, @d)',
		'nodegroups' => array('a', 'd'),
		'nodes' => array('a', 'b'),
	),
	array('expr' => '&union(@a, @d)',
		'nodegroups' => array('a', 'd'),
		'nodes' => array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'a', 'h', '1', 'b', 'i', '2', 'z'),
	),
	array('expr' => '&diff(@a, @d)',
		'nodegroups' => array('a', 'd'),
		'nodes' => array('c', 'd', 'e', 'f', 'g'),
	),

	array('expr' => '&intersect(@a, @d, @e)',
		'nodegroups' => array('a', 'd', 'e'),
		'nodes' => array(),
	),
	array('expr' => '&intersect(@a, @d, @f)',
		'nodegroups' => array('a', 'd', 'f'),
		'nodes' => array('a', 'b'),
	),
	array('expr' => '&union(@a, @d, @e)',
		'nodegroups' => array('a', 'd', 'e'),
		'nodes' => array(
		'a', 'b', 'c', 'd', 'e', 'f', 'g', 'a', 'h', '1', 'b', 'i', '2', 'z', 'c', '3', 't', 'u', 'v', 'w', 'x'),
	),
	array('expr' => '&diff(@a, @d, @e)',
		'nodegroups' => array('a', 'd', 'e'),
		'nodes' => array('d', 'e', 'f', 'g'),
	),

	array('expr' => '&union(@a, foo)',
		'nodegroups' => array('a'),
		'nodes' => array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'foo'),
		
	),
	array('expr' => '&union(@a, !c)',
		'nodegroups' => array('a'),
		'nodes' => array('a', 'b', 'd', 'e', 'f', 'g'),
	),
	array('expr' => '&union(@a, #!c)',
		'nodegroups' => array('a'),
		'nodes' => array('a', 'b', 'c', 'd', 'e', 'f', 'g'),
	),

	array('expr' => '&intersect(@a, c)',
		'nodegroups' => array('a'),
		'nodes' => array('c'),
	),
	array('expr' => '&diff(@a, c)',
		'nodegroups' => array('a'),
		'nodes' => array('a', 'b', 'd', 'e', 'f', 'g'),
	),

	array('expr' => '&intersect(@a, @d), foo',
		'nodegroups' => array('a', 'd'),
		'nodes' => array('a', 'b', 'foo'),
	),
	array('expr' => '&intersect(@a, @d), !b',
		'nodegroups' => array('a', 'd'),
		'nodes' => array('a'),
	),
	array('expr' => '&intersect(@a, @d), #b',
		'nodegroups' => array('a', 'd'),
		'nodes' => array('a', 'b'),
	),
	array('expr' => '&intersect(@a, @d), #!b',
		'nodegroups' => array('a', 'd'),
		'nodes' => array('a', 'b'),
	),
	array('expr' => '&intersect(@a, @d), #@d',
		'nodegroups' => array('a', 'd'),
		'nodes' => array('a', 'b'),
	),
	array('expr' => '&intersect(@a, @d), @d',
		'nodegroups' => array('a', 'd'),
		'nodes' => array('a', 'b', 'a', 'h', '1', 'b', 'i', '2', 'z'),
	),

	array('expr' => 'foo, &intersect(@a, @d)',
		'nodegroups' => array('a', 'd'),
		'nodes' => array('foo', 'a', 'b'),
	),
	array('expr' => '!b, &intersect(@a, @d)',
		'nodegroups' => array('a', 'd'),
		'nodes' => array('a'),
	),
	array('expr' => '#b, &intersect(@a, @d)',
		'nodegroups' => array('a', 'd'),
		'nodes' => array('a', 'b'),
	),
	array('expr' => '#!b, &intersect(@a, @d)',
		'nodegroups' => array('a', 'd'),
		'nodes' => array('a', 'b'),
	),
	array('expr' => '#@d, &intersect(@a, @d)',
		'nodegroups' => array('a', 'd'),
		'nodes' => array('a', 'b'),
	),
	array('expr' => '@d, &intersect(@a, @d)',
		'nodegroups' => array('a', 'd'),
		'nodes' => array('a', 'h', '1', 'b', 'i', '2', 'z', 'a', 'b'),
	),

	array('expr' => 'foo, &intersect(@a, @d), &diff(@a, @b)',
		'nodegroups' => array('a', 'b', 'd'),
		'nodes' => array('foo', 'a', 'b', 'a', 'b', 'c', 'd', 'e', 'f', 'g'),
	),
	array('expr' => '!b, &intersect(@a, @d), &diff(@a, @b)',
		'nodegroups' => array('a', 'b', 'd'),
		'nodes' => array('a', 'a', 'c', 'd', 'e', 'f', 'g'),
	),
	array('expr' => '#b, &intersect(@a, @d), &diff(@a, @b)',
		'nodegroups' => array('a', 'b', 'd'),
		'nodes' => array('a', 'b', 'a', 'b', 'c', 'd', 'e', 'f', 'g'),
	),
	array('expr' => '#!b, &intersect(@a, @d), &diff(@a, @b)',
		'nodegroups' => array('a', 'b', 'd'),
		'nodes' => array('a', 'b', 'a', 'b', 'c', 'd', 'e', 'f', 'g'),
	),
	array('expr' => '#@d, &intersect(@a, @d), &diff(@a, @b)',
		'nodegroups' => array('a', 'b', 'd'),
		'nodes' => array('a', 'b', 'a', 'b', 'c', 'd', 'e', 'f', 'g'),
	),
	array('expr' => '@d, &intersect(@a, @d), &diff(@a, @b)',
		'nodegroups' => array('a', 'b', 'd'),
		'nodes' => array('a', 'h', '1', 'b', 'i', '2', 'z', 'a', 'b', 'a', 'b', 'c', 'd', 'e', 'f', 'g'),
	),

	array('expr' => '&intersect(@a, @d), &diff(@a, @b)',
		'nodegroups' => array('a', 'b', 'd'),
		'nodes' => array('a', 'b', 'a', 'b', 'c', 'd', 'e', 'f', 'g'),
	),

	array('expr' => '&regexp(/^a$/, @b)',
		'nodegroups' => array('b'),
		'nodes' => array(),
	),
	array('expr' => '&regexp(/^foo$/, foo, bar)',
		'nodegroups' => array(),
		'nodes' => array('foo'),
	),
	array('expr' => '&regexp(/^foo/, foo, bar, foobar)',
		'nodegroups' => array(),
		'nodes' => array('foo', 'foobar'),
	),
	array('expr' => '&regexp(/^foo1\./, foo, bar, foo1bar, foo1.bar)',
		'nodegroups' => array(),
		'nodes' => array('foo1.bar'),
	),
	array('expr' => '&regexp(/^a$/, @a)',
		'nodegroups' => array('a'),
		'nodes' => array('a'),
	),
	array('expr' => '&regexp(/^a$/i, @a, @b)',
		'nodegroups' => array('a', 'b'),
		'nodes' => array('a'),
	),
	array('expr' => '&regexp(/^A$/i, @a, @b)',
		'nodegroups' => array('a', 'b'),
		'nodes' => array('a'),
	),
	array('expr' => '&regexp(/^A$/, @a, @b)',
		'nodegroups' => array('a', 'b'),
		'nodes' => array(),
	),
	array('expr' => '&regexp(/^a$/, @a, @b)',
		'nodegroups' => array('a', 'b'),
		'nodes' => array('a'),
	),
	array('expr' => '&regex(/^foo/, foo, bar, foobar)',
		'nodegroups' => array(),
		'nodes' => array('foo', 'foobar'),
		
	),
	array('expr' => '&regex(/fo(o|p)/, foo, bar, fop)',
		'nodegroups' => array(),
		'nodes' => array('foo', 'fop'),
	),
);
?>
