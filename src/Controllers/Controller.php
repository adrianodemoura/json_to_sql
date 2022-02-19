<?php
declare(strict_types=1);

namespace JsonToSql\Controllers;

use JsonToSql\Controllers\Controller as BaseController;

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

			if (! empty( $this->config[ $stringConfig ] ) ) {
				if ( strpos( $valor, '-') > -1 || strpos( $valor, '--' ) > -1 ) {
					$stringConfig = '';
				}
			}

			if ( strlen( trim ( $stringConfig ) ) ) {
				$this->config[ $stringConfig ][] = $valor;
			}

			if ( strpos( $valor, '-') > -1 || strpos( $valor, '--' ) > -1 ) {
				$stringConfig = str_replace( ['-','--'], '', $valor );
				$this->config[ $stringConfig ] = [];
			}
		}
	}
}