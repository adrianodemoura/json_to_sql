<?php
declare(strict_types=1);

namespace JsonToSql\Traits;

trait TimePiece {
	/**
	 * Lista de logs
	 * 
	 * @var array
	 */
	private $logList = [];

	/**
	 * Inicia o timer
	 * 
	 * @return 	void
	 */
	public function startTime() {
		$this->logList['inicio'] = date("d/m/Y H:i:s");
	}

	/**
	 * Finaliza o timer
	 * 
	 * @return 	void
	 */
	public function endTime() {
		$this->logList['ini'] 	= $this->logList['inicio'];
		$this->logList['fim'] 	= date("d/m/Y H:i:s");
		unset( $this->logList['inicio']  );
	}

	/**
	 * Printa o LOG
	 */
	public function printTime(string $titulo='') {

		$logList = (!empty($titulo)) ? [$titulo=>$this->logList[$titulo]] : $this->logList;

		foreach($logList as $_titulo => $_info ) {

			$titulo = substr( $_titulo . ' '.str_repeat('.', 20 ), 0, 20 );

			echo "$titulo: $_info";

			if ( PHP_SAPI === 'cli' ) echo "\n";

		}
	}

	/**
	 * Atualiza a lista de logs com texto e não array.
	 * 
	 * @param 	string 	$titulo 	Título do log.
	 * @param 	string 	$texto 		Texto do log.
	 * @return 	void
	 */
	public function addTime( string $titulo='', string $texto='' ) {
		$this->logList[ $titulo ] = $texto;
	}
		
}