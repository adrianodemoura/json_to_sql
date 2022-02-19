<?php
declare(strict_types=1);

namespace JsonToSql\Controllers;

use JsonToSql\Controllers\Controller as BaseController;
use JsonToSql\Traits\TimePiece;
use JsonToSql\Utility\Inflector;
use Exception;

class CreateController extends BaseController {

	use TimePiece;

	private function validaParams() {
		if ( !isset( $this->params[1] ) ) {
			throw new Exception( 'Informe o nome do arquivo JSON !' );
		}

		if ( !file_exists( STORAGE . "/tmp/json/" . explode(",", $this->params[1] )[0] ) ) {
			throw new Exception( "Não foi possivel localizar o arquivo \"$jsonFile\", certifique-se se ele existe no diretório ".STORAGE . "/tmp/json" );
		}
	}

	public function execute( ) {

		$this->startTime();

		$this->validaParams();

		$stringCampos 	= '';
		$jsonArray 		= $this->getFileJsonToArray();

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