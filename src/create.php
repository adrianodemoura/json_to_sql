<?php
declare(strict_types=1);

try
{
	define( 'DIR_JSON_TO_SQL', str_replace( ['/includes', '/bin', '/src', '/Core'], '', __DIR__ ) );

	include_once DIR_JSON_TO_SQL . '/src/bootstrap.php';

	include_once DIR_JSON_TO_SQL . '/vendor/autoload.php';

	include_once DIR_JSON_TO_SQL . '/src/global.php';

	if ( !isset( $_SERVER['argv'][1] ) ) {
		throw new Exception( 'Informe o nome do arquivo JSON !' );
	}

	$controllerFile = ucfirst( strtolower( str_replace( ['bin/'], '', explode(",", $_SERVER['argv'][0] )[0] ) ) );
	$jsonFile = explode(",", $_SERVER['argv'][1] )[0];

	if ( !file_exists( DIR_JSON_TO_SQL . DS . $jsonFile ) ) {
		throw new Exception( "Não foi possivel localizar o arquivo \"$jsonFile\", verifique se ele existe no diretório raiz." );
	}

	$fullClass 	= "JsonToSql\\Controllers\\{$controllerFile}Controller";

	$Objeto     = new $fullClass ( );

	$Objeto->execute();

} catch ( Exception $e )
{
	switch ( $e->getCode() )
	{
		case 18001: // $ bin/create --tag
			include_once DIR_JSON_TO_SQL . '/docs/help/tag';
			break;

		default:
			echo "error: {$e->getMessage()} \n";
			break;
	}	
}
