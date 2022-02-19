<?php
declare(strict_types=1);

namespace JsonToSql\Controllers;

use JsonToSql\Controllers\Controller as BaseController;
use JsonToSql\Traits\TimePiece;
use JsonToSql\Utility\Inflector;
use Exception;

class CreateController extends BaseController {

	use TimePiece;

	public function execute( ) {

		$this->startTime();

		$stringCampos = '';

		$jsonFile = explode(",", $this->params[1] )[0];
		if (! file_exists( DIR_JSON_TO_SQL . DS . $jsonFile ) ) {
			throw new Exception( "Não foi possivel localizar o arquivo \"$jsonFile\", verifique se ele existe no diretório raiz." );
		}

		$jsonArray = @json_decode( file_get_contents( DIR_JSON_TO_SQL . DS. $jsonFile ), true)['content'][0];
		if ( empty( $jsonArray ) ) {
			throw new Exception ( "a tag \"content\" não foi localizada no arquivo json !" );
		}

		foreach( $jsonArray as $chave => $valorChave ) {

			if ( in_array( $chave, $this->config['ignore'] ) ) {
				continue;
			}

			if (! is_array( $valorChave ) ) {
				$stringCampos .= Inflector::underscore( $chave ) . ', ';
			} else {
				//dump( $chave.' - '.Inflector::underscore( $chave ) );
			}
		}

		dump( $stringCampos );

		$this->addTime( 'SUCESSO', 'Arquivo gerado com sucesso.' );		

		$this->endTime();
		$this->printTime();
	}

}