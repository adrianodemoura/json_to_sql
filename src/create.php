<?php
declare(strict_types=1);

try
{
	if ( !defined('DIR_APP') ) {
		define( 'DIR_APP', './' );
	}

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

} catch ( Exception $e )
{
	switch ( $e->getCode() )
	{
		case 300: // $ bin/create --help
			if ( PHP_SAPI === 'cli' ) {
				echo "\n";
			}
			break;

		default:
			echo "error: {$e->getMessage()}";
			if ( PHP_SAPI === 'cli' ) {
				echo "\n";
			}
			break;
	}	
}
