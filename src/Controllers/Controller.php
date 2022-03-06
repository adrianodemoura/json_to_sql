<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Controller as BaseController;

class Controller {

	protected $params = [];

	protected $config = [ 'ignore' => [] ];

	public function __construct ( ) {

		$this->params = isset( $_SERVER[ 'argv' ] ) ? $_SERVER[ 'argv' ] : [];

		$this->setParamsConfig();

	}

	private function setParamsConfig( ) {
		$stringConfig = '';

		foreach( $this->params as $l => $valor ) {

			if ( $l < 2 ) {
				continue; 
			}

			$arrValor = explode('=', $valor );

			if ( isset($arrValor[1]) ) {

				$stringConfig = str_replace( '--', '', $arrValor[0] );

				$this->config[ $stringConfig ] = $arrValor[1];
			
				if ( in_array( $stringConfig, ['ignore'] ) ) {
			
					$this->config[ $stringConfig ] = explode( ',', $arrValor[1] );
				} 
			}
		}
	}

	protected function getFileJsonToArray() {
		$jsonFile = explode(",", $this->params[1] )[0] . '.json';
	
		$jsonArray = @json_decode( file_get_contents( STORAGE . "/tmp/json/$jsonFile" ), true)['content'][0];
		
		if ( empty( $jsonArray ) ) {

			throw new Exception ( "a tag \"content\" não foi localizada no arquivo " . STORAGE . "/tmp/json/$jsonFile" );
		}

		return $jsonArray;
	}
}