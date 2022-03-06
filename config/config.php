<?php

return [

	'app' 						=> [
		'debug' 				=> true,
		'force_create' 			=> true,
	],

	'params_fields' 			=> [
		'max_width' 			=> 30,
		'max_width_word' 		=> 5,
		'first_word_complete' 	=> true,
		'default_string_width' 	=> 200,
		'ignore' 				=> ['sid'],
	],

	'database' 					=> [
		'username' 				=> 'jsonsql_us',
		'password' 				=> 'jsonsql_67',
		'database' 				=> 'jsonsql_bd',
		'host' 					=> 'localhost',
		'driver' 				=> 'mysql'
	]

];
