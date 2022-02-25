<?php

declare(strict_types=1);

if ( !defined('DIR_APP') ) { define( 'DIR_APP', './' ); }

// separador
if ( !defined('DS') )  { define( 'DS', DIRECTORY_SEPARATOR ); }

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

try {
	include_once DIR_APP . '/vendor/autoload.php';

	include_once DIR_APP . '/src/bootstrap.php';

	include_once DIR_APP . '/src/global.php';

	if ( !isset( $_SERVER['argv'][0] ) ) {

		throw new Exception( 'Informe o serviço desejado !' );
	}

	$controllerFile = ucfirst( strtolower( str_replace( ['bin/'], '', explode(",", $_SERVER['argv'][0] )[0] ) ) );

	if ( $_SERVER['argv'][1] === '--help' ) {

		include_once DIR_APP . '/docs/help/' . strtolower( $controllerFile ) . '.txt';

		throw new Exception ( 'ajuda', 300 );
	}

	$fullClass 	= "App\\Controllers\\{$controllerFile}Controller";

	$Objeto     = new $fullClass ( );

	$Objeto->execute();

} catch ( Exception $e ) {
	switch ( $e->getCode() )
	{
		case 300: // $ bin/create --help

			br2();

			break;

		default:

			success( "error: {$e->getMessage()}" );

			br2();

			break;
	}	
}