<?php

/*

$vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);

*/

// separador
if ( !defined('DS') )  define( 'DS', DIRECTORY_SEPARATOR );

// diretório aonde está o JsonToSql
if ( !defined('DIR_JSON_TO_SQL') ) define( 'DIR_JSON_TO_SQL', str_replace( ['/src', '/Core'], '', dirname( __DIR__ ) ) );

// diretório da aplicação
if ( !defined('DIR_APP') )
{
	if ( strpos( DIR_JSON_TO_SQL, '/vendor/adrianodemoura/json_to_sql') > -1 )
		define( 'DIR_APP', str_replace( ['/vendor', '/adrianodemoura', '/json_to_sql'], '', DIR_JSON_TO_SQL ) );
	else 
		define( 'DIR_APP', DIR_JSON_TO_SQL );
}

// diretório temporário
if ( !defined('TMP') )
{
	if ( is_dir( DIR_APP . '/tmp' ) ) 
		define( 'TMP', DIR_APP . '/tmp' );
	else
		define( 'TMP', '/tmp' );
}

// autoload da aplicação
require DIR_APP . '/vendor/autoload.php';

// funções globais
require DIR_JSON_TO_SQL . '/src/global.php';
