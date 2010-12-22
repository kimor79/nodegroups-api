#!/usr/local/bin/php
<?php

require('./nodegroups_test.class');

$ng = new NodeGroupsTests();

$tests = array(
	array('expr' => 'foo, bar', 'result' => 'foo, bar'),
	array('expr' => 'foo, bar, !bar', 'result' => 'foo'),
	array('expr' => 'foo, bar, #bar', 'result' => 'foo, bar'),

	array('expr' => 'foo, !bar, bar', 'result' => 'foo'),
	array('expr' => 'foo, #bar, bar', 'result' => 'foo, bar'),

	array('expr' => 'foo, #bar, !bar', 'result' => 'foo'),

	array('expr' => '@a', 'result' => 'a, b, c, d, e, f, g'),
	array('expr' => '@a, @b', 'result' => 'a, b, c, d, e, f, g, h, i, j, k, l, m, n'),
	array('expr' => '@a, @b, !f', 'result' => 'a, b, c, d, e, g, h, i, j, k, l, m, n'),
	array('expr' => '@a, @b, #!f', 'result' => 'a, b, c, d, e, f, g, h, i, j, k, l, m, n'),

	array('expr' => '@a, !@b', 'result' => 'a, b, c, d, e, f, g'),
	array('expr' => '@a, !@d', 'result' => 'c, d, e, f, g'),
	array('expr' => '@g, !@a, !@b', 'result' => 'o, p, q, r, s, t, u, v, w, x, y, z'),
	array('expr' => '@g, !@a, !@d', 'result' => 'j, k, l, m, n, o, p, q, r, s, t, u, v, w, x, y'),
	array('expr' => '@g, !@a, !@h', 'result' => 'k, l, m, n, o, p, q, r, s, t, u, v, w, x, y, z'),
	array('expr' => '@g, !@h, !@a', 'result' => 'k, l, m, n, o, p, q, r, s, t, u, v, w, x, y, z'),

	array('expr' => '&intersect(@a, @b)', 'result' => ''),
	array('expr' => '&union(@a, @b)', 'result' => 'a, b, c, d, e, f, g, h, i, j, k, l, m, n'),
	array('expr' => '&diff(@a, @b)', 'result' => 'a, b, c, d, e, f, g'),

	array('expr' => '&intersect(@a, @d)', 'result' => 'a, b'),
	array('expr' => '&union(@a, @d)', 'result' => 'a, b, c, d, e, f, g, a, h, 1, b, i, 2, z'),
	array('expr' => '&diff(@a, @d)', 'result' => 'c, d, e, f, g'),

	array('expr' => '&intersect(@a, @d, @e)', 'result' => ''),
	array('expr' => '&intersect(@a, @d, @f)', 'result' => 'a, b'),
	array('expr' => '&union(@a, @d, @e)', 'result' => 'a, b, c, d, e, f, g, a, h, 1, b, i, 2, z, c, 3, t, u, v, w, x'),
	array('expr' => '&diff(@a, @d, @e)', 'result' => 'd, e, f, g'),

	array('expr' => '&union(@a, foo)', 'result' => 'a, b, c, d, e, f, g, foo'),
	array('expr' => '&union(@a, !c)', 'result' => 'a, b, d, e, f, g'),
	array('expr' => '&union(@a, #!c)', 'result' => 'a, b, c, d, e, f, g'),

	array('expr' => '&intersect(@a, c)', 'result' => 'c'),
	array('expr' => '&diff(@a, c)', 'result' => 'a, b, d, e, f, g'),

	array('expr' => '&intersect(@a, @d), foo', 'result' => 'a, b, foo'),
	array('expr' => '&intersect(@a, @d), !b', 'result' => 'a'),
	array('expr' => '&intersect(@a, @d), #b', 'result' => 'a, b'),
	array('expr' => '&intersect(@a, @d), #!b', 'result' => 'a, b'),
	array('expr' => '&intersect(@a, @d), #@d', 'result' => 'a, b'),
	array('expr' => '&intersect(@a, @d), @d', 'result' => 'a, b, a, h, 1, b, i, 2, z'),

	array('expr' => 'foo, &intersect(@a, @d)', 'result' => 'foo, a, b'),
	array('expr' => '!b, &intersect(@a, @d)', 'result' => 'a'),
	array('expr' => '#b, &intersect(@a, @d)', 'result' => 'a, b'),
	array('expr' => '#!b, &intersect(@a, @d)', 'result' => 'a, b'),
	array('expr' => '#@d, &intersect(@a, @d)', 'result' => 'a, b'),
	array('expr' => '@d, &intersect(@a, @d)', 'result' => 'a, h, 1, b, i, 2, z, a, b'),

	array('expr' => 'foo, &intersect(@a, @d), &diff(@a, @b)', 'result' => 'foo, a, b, a, b, c, d, e, f, g'),
	array('expr' => '!b, &intersect(@a, @d), &diff(@a, @b)', 'result' => 'a, a, c, d, e, f, g'),
	array('expr' => '#b, &intersect(@a, @d), &diff(@a, @b)', 'result' => 'a, b, a, b, c, d, e, f, g'),
	array('expr' => '#!b, &intersect(@a, @d), &diff(@a, @b)', 'result' => 'a, b, a, b, c, d, e, f, g'),
	array('expr' => '#@d, &intersect(@a, @d), &diff(@a, @b)', 'result' => 'a, b, a, b, c, d, e, f, g'),
	array('expr' => '@d, &intersect(@a, @d), &diff(@a, @b)', 'result' => 'a, h, 1, b, i, 2, z, a, b, a, b, c, d, e, f, g'),

	array('expr' => '&intersect(@a, @d), &diff(@a, @b)', 'result' => 'a, b, a, b, c, d, e, f, g'),

	array('expr' => '&regexp(/^a$/, @b)', 'result' => ''),
	array('expr' => '&regexp(/^foo$/, foo, bar)', 'result' => 'foo'),
	array('expr' => '&regexp(/^foo/, foo, bar, foobar)', 'result' => 'foo, foobar'),
	array('expr' => '&regexp(/^foo1\./, foo, bar, foo1bar, foo1.bar)', 'result' => 'foo1.bar'),
	array('expr' => '&regexp(/^a$/, @a)', 'result' => 'a'),
	array('expr' => '&regexp(/^a$/i, @a, @b)', 'result' => 'a'),
	array('expr' => '&regexp(/^A$/i, @a, @b)', 'result' => 'a'),
	array('expr' => '&regexp(/^A$/, @a, @b)', 'result' => ''),
	array('expr' => '&regexp(/^a$/, @a, @b)', 'result' => 'a'),
	array('expr' => '&regex(/^foo/, foo, bar, foobar)', 'result' => 'foo, foobar'),
	array('expr' => '&regex(/fo(o|p)/, foo, bar, fop)', 'result' => 'foo, fop'),
);

$pass = 0;

foreach($tests as $pos => $test) {
	$result = implode(", ", $ng->parseExpression($test['expr']));

	if($result !== $test['result']) {
		fwrite(STDERR, sprintf("FAIL (%d): %s\n\tgot: %s\n\texpected: %s\n", $pos, $test['expr'], $result, $test['result']));
	} else {
//		fwrite(STDOUT, sprintf("PASS (%d): %s\n\tgot: %s\n\texpected: %s\n", $pos, $test['expr'], $result, $test['result']));
		$pass++;
	}
}

printf("\nPASS: %d/%d\n", $pass, count($tests));

?>
