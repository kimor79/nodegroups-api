my $uri = 'http://' . $ENV{'MY_VM'} .
	'/nodegroups/api/v2/v/validate_nodegroup_name.php';

$TESTS = [

{
	'description' => 'v2/v/validate_nodegroup_name.php - Missing fields',
	'uri' => $uri,
	'requests' => [
		{
			'json' => {
				'foo' => $UNIQUE,
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => any({}, []),
				'message' => re('Missing'),
				'status' => 400,
			},
		},
	],
},

{
	'description' => 'v2/v/validate_nodegroup_name.php - Extra fields',
	'uri' => $uri,
	'requests' => [
		{
			'json' => {
				'foo' => 'test',
				'nodegroup' => 'test',
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => any({}, []),
				'message' => re('Extra'),
				'status' => 400,
			},
		},
	],
},

{
	'description' => 'v2/v/validate_nodegroup_name.php - Invalid nodegroup',
	'uri' => $uri,
	'requests' => [
		{
			'json' => {
				'nodegroup' => 'space space',
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => any({}, []),
				'message' => re('Invalid'),
				'status' => 400,
			},
		},
	],
},

{
	'description' => 'v2/v/validate_nodegroup_name.php - Multiple nodegroups',
	'uri' => $uri,
	'requests' => [
		{
			'json' => {
				'nodegroup' => [ 
					'the_first',
					'the_second',
				],
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => any({}, []),
				'message' => 'Multiple "nodegroup" not allowed',
				'status' => 400,
			},
		},
	],
},

{
	'description' => 'v2/v/validate_nodegroup_name.php - Good 1',
	'uri' => $uri,
	'requests' => [
		{
			'json' => {
				'nodegroup' => 'good1' . $UNIQUE,
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => {
					'nodegroup' => 'good1' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 200,
			},
		},
	],
},

{
	'description' => 'v2/v/validate_nodegroup_name.php - Good 2',
	'uri' => $uri,
	'requests' => [
		{
			'json' => {
				'nodegroup' => 'GooD2' . $UNIQUE,
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => {
					'nodegroup' => 'good2' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 200,
			},
		},
	],
},

];
