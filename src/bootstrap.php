<?php

// separador
if ( !defined('DS') )  define( 'DS', DIRECTORY_SEPARATOR );

// diretório temporário
if ( !defined('TMP') )
{
	if ( is_dir( DIR_APP . '/storage/tmp' ) ) 
		define( 'TMP', DIR_APP . '/storage/tmp' );
	else
		define( 'TMP', sys_get_temp_dir() );
}

// diretório storage
if ( !defined('STORAGE') )
{
	if ( is_dir( DIR_APP . '/storage' ) ) 
		define( 'STORAGE', DIR_APP . '/storage' );
	else
		define( 'STORAGE', '/storage' );
}

// autoload da aplicação
require DIR_APP . '/vendor/autoload.php';

// funções globais
require DIR_APP . '/src/global.php';
