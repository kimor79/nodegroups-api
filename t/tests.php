<?php

$tests = array(
	array('expr' => 'foo, bar', 'nodes' => 'foo, bar'),
	array('expr' => 'foo, bar, !bar', 'nodes' => 'foo'),
	array('expr' => 'foo, bar, #bar', 'nodes' => 'foo, bar'),

	array('expr' => 'foo, !bar, bar', 'nodes' => 'foo'),
	array('expr' => 'foo, #bar, bar', 'nodes' => 'foo, bar'),

	array('expr' => 'foo, #bar, !bar', 'nodes' => 'foo'),

	array('expr' => '@a', 'nodes' => 'a, b, c, d, e, f, g'),
	array('expr' => '@a, @b', 'nodes' =>
		'a, b, c, d, e, f, g, h, i, j, k, l, m, n'),
	array('expr' => '@a, @b, !f', 'nodes' =>
		'a, b, c, d, e, g, h, i, j, k, l, m, n'),
	array('expr' => '@a, @b, #!f', 'nodes' =>
		'a, b, c, d, e, f, g, h, i, j, k, l, m, n'),

	array('expr' => '@a, !@b', 'nodes' => 'a, b, c, d, e, f, g'),
	array('expr' => '@a, !@d', 'nodes' => 'c, d, e, f, g'),
	array('expr' => '@g, !@a, !@b', 'nodes' =>
		'o, p, q, r, s, t, u, v, w, x, y, z'),
	array('expr' => '@g, !@a, !@d', 'nodes' =>
		'j, k, l, m, n, o, p, q, r, s, t, u, v, w, x, y'),
	array('expr' => '@g, !@a, !@h', 'nodes' =>
		'k, l, m, n, o, p, q, r, s, t, u, v, w, x, y, z'),
	array('expr' => '@g, !@h, !@a', 'nodes' =>
		'k, l, m, n, o, p, q, r, s, t, u, v, w, x, y, z'),

	array('expr' => '&intersect(@a, @b)', 'nodes' => ''),
	array('expr' => '&union(@a, @b)', 'nodes' =>
		'a, b, c, d, e, f, g, h, i, j, k, l, m, n'),
	array('expr' => '&diff(@a, @b)', 'nodes' => 'a, b, c, d, e, f, g'),

	array('expr' => '&intersect(@a, @d)', 'nodes' => 'a, b'),
	array('expr' => '&union(@a, @d)', 'nodes' =>
		'a, b, c, d, e, f, g, a, h, 1, b, i, 2, z'),
	array('expr' => '&diff(@a, @d)', 'nodes' => 'c, d, e, f, g'),

	array('expr' => '&intersect(@a, @d, @e)', 'nodes' => ''),
	array('expr' => '&intersect(@a, @d, @f)', 'nodes' => 'a, b'),
	array('expr' => '&union(@a, @d, @e)', 'nodes' =>
		'a, b, c, d, e, f, g, a, h, 1, b, i, 2, z, c, 3, t, u, v, w, x'),
	array('expr' => '&diff(@a, @d, @e)', 'nodes' => 'd, e, f, g'),

	array('expr' => '&union(@a, foo)', 'nodes' =>
		'a, b, c, d, e, f, g, foo'),
	array('expr' => '&union(@a, !c)', 'nodes' => 'a, b, d, e, f, g'),
	array('expr' => '&union(@a, #!c)', 'nodes' => 'a, b, c, d, e, f, g'),

	array('expr' => '&intersect(@a, c)', 'nodes' => 'c'),
	array('expr' => '&diff(@a, c)', 'nodes' => 'a, b, d, e, f, g'),

	array('expr' => '&intersect(@a, @d), foo', 'nodes' => 'a, b, foo'),
	array('expr' => '&intersect(@a, @d), !b', 'nodes' => 'a'),
	array('expr' => '&intersect(@a, @d), #b', 'nodes' => 'a, b'),
	array('expr' => '&intersect(@a, @d), #!b', 'nodes' => 'a, b'),
	array('expr' => '&intersect(@a, @d), #@d', 'nodes' => 'a, b'),
	array('expr' => '&intersect(@a, @d), @d', 'nodes' =>
		'a, b, a, h, 1, b, i, 2, z'),

	array('expr' => 'foo, &intersect(@a, @d)', 'nodes' => 'foo, a, b'),
	array('expr' => '!b, &intersect(@a, @d)', 'nodes' => 'a'),
	array('expr' => '#b, &intersect(@a, @d)', 'nodes' => 'a, b'),
	array('expr' => '#!b, &intersect(@a, @d)', 'nodes' => 'a, b'),
	array('expr' => '#@d, &intersect(@a, @d)', 'nodes' => 'a, b'),
	array('expr' => '@d, &intersect(@a, @d)', 'nodes' =>
		'a, h, 1, b, i, 2, z, a, b'),

	array('expr' => 'foo, &intersect(@a, @d), &diff(@a, @b)', 'nodes' =>
		'foo, a, b, a, b, c, d, e, f, g'),
	array('expr' => '!b, &intersect(@a, @d), &diff(@a, @b)', 'nodes' =>
		'a, a, c, d, e, f, g'),
	array('expr' => '#b, &intersect(@a, @d), &diff(@a, @b)', 'nodes' =>
		'a, b, a, b, c, d, e, f, g'),
	array('expr' => '#!b, &intersect(@a, @d), &diff(@a, @b)', 'nodes' =>
		'a, b, a, b, c, d, e, f, g'),
	array('expr' => '#@d, &intersect(@a, @d), &diff(@a, @b)', 'nodes' =>
		'a, b, a, b, c, d, e, f, g'),
	array('expr' => '@d, &intersect(@a, @d), &diff(@a, @b)', 'nodes' =>
		'a, h, 1, b, i, 2, z, a, b, a, b, c, d, e, f, g'),

	array('expr' => '&intersect(@a, @d), &diff(@a, @b)', 'nodes' =>
		'a, b, a, b, c, d, e, f, g'),

	array('expr' => '&regexp(/^a$/, @b)', 'nodes' => ''),
	array('expr' => '&regexp(/^foo$/, foo, bar)', 'nodes' => 'foo'),
	array('expr' => '&regexp(/^foo/, foo, bar, foobar)', 'nodes' =>
		'foo, foobar'),
	array('expr' => '&regexp(/^foo1\./, foo, bar, foo1bar, foo1.bar)',
		'nodes' => 'foo1.bar'),
	array('expr' => '&regexp(/^a$/, @a)', 'nodes' => 'a'),
	array('expr' => '&regexp(/^a$/i, @a, @b)', 'nodes' => 'a'),
	array('expr' => '&regexp(/^A$/i, @a, @b)', 'nodes' => 'a'),
	array('expr' => '&regexp(/^A$/, @a, @b)', 'nodes' => ''),
	array('expr' => '&regexp(/^a$/, @a, @b)', 'nodes' => 'a'),
	array('expr' => '&regex(/^foo/, foo, bar, foobar)', 'nodes' =>
		'foo, foobar'),
	array('expr' => '&regex(/fo(o|p)/, foo, bar, fop)', 'nodes' =>
		'foo, fop'),
);
?>
