<?php
declare(strict_types=1);

namespace JsonToSql\Controllers;

use JsonToSql\Controllers\Controller as BaseController;
use JsonToSql\Traits\TimePiece;
use JsonToSql\Utility\Inflector;
use JsonToSql\Utility\Message as MSG;
use JsonToSql\Utility\SqlManage;
use JsonToSql\Database\Schema\TableSchema;
use Exception;

class CreateController extends BaseController {

	use TimePiece;

	private function validaParams() {
		if ( !isset( $this->params[1] ) ) {
			throw new Exception( 'Informe o nome do arquivo JSON !' );
		}

		if ( !file_exists( STORAGE . "/tmp/json/" . explode(",", $this->params[1] )[0] ) ) {
			throw new Exception( MSG::get( '0005', [ $this->params[1], "/tmp/json" ] ) );
		}

		$config = [ 'id_auto'=>false, 'driver'=>'mysql', 'table_name'=> str_replace( ".json", "", $this->params[1] ) ];

		foreach( $this->params as $_chave => $_valor ) {
			if ( strpos( '--mysql=', $_valor) > -1 ) {
				$config['driver'] = str_replace( '--mysql=', '', $_valor );
			}
			if ( strpos( '--id-auto', $_valor ) > -1 ) {
				$config['id_auto'] = true;
			}
		}

		$this->TableSchema = new TableSchema( $config );
	}

	public function execute() {

		$this->startTime();

		$this->validaParams();

		$stringCampos 	= '';
		$jsonArray 		= $this->getFileJsonToArray();
		$fileOut  		= str_replace( ".json", ".sql", $this->params[1] );
		$dirEscrita  	= "/storage/tmp/sql";
		$arrCampos  	= [];
		$scriptSqlCreate= "CREATE TABLE ".$this->TableSchema->getConfig('table_name')." (\n{campos}) {complement};\n";

		foreach( $jsonArray as $chave => $valorChave ) {

			if ( in_array( $chave, $this->config['ignore'] ) ) {
				continue;
			}

			if (! is_array( $valorChave ) ) {
				$arrCampos[] = Inflector::underscore( $chave );
			}
		}

		$scriptSqlCreate = str_replace( "{campos}", 	$this->TableSchema->getFields( $arrCampos ), $scriptSqlCreate );
		$scriptSqlCreate = str_replace( "{complement}", $this->TableSchema->getComplementTable ( ) , $scriptSqlCreate );

		if (! file_put_contents( DIR_APP . $dirEscrita.'/'.$fileOut, $scriptSqlCreate ) ) {
			throw new Exception( MSG::get('0007', [$fileOut, $dirEscrita ] ) );
		}

		$this->addTime( 'SUCESSO', MSG::get('0006', [ $fileOut, $dirEscrita ] ) );		

		$this->endTime();
		$this->printTime();
	}

}