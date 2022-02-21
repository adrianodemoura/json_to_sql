<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Controller as BaseController;
use App\Traits\TimePiece;
use App\Utility\Inflector;
use App\Utility\Message as MSG;
use App\Utility\SqlManage;
use App\Database\Schema\TableSchema;
use App\Configure\Configure;
use Exception;

class CreateController extends BaseController {

	use TimePiece;

	public function execute() {

		$this->validate();

		$this->startTime();

		$this->setTableSchema();
		
		$fileOut  			= str_replace( ".json", ".sql", $this->params[1] );
		$dirEscrita  		= "/storage/tmp/sql";		
		$scriptSqlCreate 	= $this->getScriptSqlCreate();

		if (! file_put_contents( DIR_APP . $dirEscrita.'/'.$fileOut, $scriptSqlCreate ) ) {
			throw new Exception( MSG::get('0007', [$fileOut, $dirEscrita ] ) );
		}

		$this->addTime( 'SUCESSO', MSG::get('0006', [ $fileOut, $dirEscrita ] ) );		

		$this->endTime();

		$this->printTime();
	}

	private function validate() {
		if ( !isset( $this->params[1] ) ) {
			throw new Exception( 'Informe o nome do arquivo JSON !' );
		}

		if ( !file_exists( STORAGE . "/tmp/json/" . explode(",", $this->params[1] )[0] ) ) {
			throw new Exception( MSG::get( '0005', [ $this->params[1], "/tmp/json" ] ) );
		}
	}

	private function setTableSchema() {

		$config = [ 'table_name'=> str_replace( ".json", "", $this->params[1] ) ];

		foreach( $this->params as $_chave => $_valor ) {

			if ( strpos( $_valor, '--driver=' ) > -1 ) {
				$config['driver'] = str_replace( ['--driver=', '--driver', ' -d ', ' --d ', '=', ' '], '', $_valor );
			}

			if ( strpos( $_valor, '--prefix-table-name=' ) > -1 ) {
				$config['prefix_table_name'] = str_replace( ['--prefix-table-name=', '=', ' '], '', $_valor );
			}
		}

		$this->TableLeft = new TableSchema( $config );
	}

	private function getScriptSqlCreate() : string {

		$scriptSqlAssociation= '';
		$tableLeft 			=  $this->TableLeft->getConfig('prefix_table_name') . $this->TableLeft->getConfig('table_name');

		$scriptSqlCreate 	= "\nCREATE TABLE {$tableLeft} (\n{campos}) {complement};\n";

		$scriptSqlCreate 	= str_replace( "{campos}", 	$this->TableLeft->getFields( $this->getArrFields() ), $scriptSqlCreate );

		$scriptSqlCreate 	= str_replace( "{complement}", $this->TableLeft->getComplementTable ( ) , $scriptSqlCreate );

		$scriptSqlAssociations = '';

		foreach( $this->TableLeft->getAssociation() as $_fieldAssociation => $_fieldChave ) {

			$tableNameRight		= Inflector::pluralize( Inflector::underscore( $_fieldChave ) );

			$prefixTableLeft 	= $this->TableLeft->getConfig('prefix_table_name');

			$tableRight 		= new TableSchema( [ 'table_name'=>$tableNameRight, 'driver'=> $this->TableLeft->getConfig('driver'), 'prefix_table_name'=>$prefixTableLeft ] );

			$scriptTableRight 	= ( $tableRight->getConfig('drop_table') === true ) ? $tableRight->getDropTable().';' : '';

			$scriptTableRight   .= "\nCREATE TABLE {$prefixTableLeft}{$tableNameRight} (\n{campos}\n) {complement};\n";

			$scriptTableRight 	= str_replace( "{campos}", $tableRight->getFields( $this->getArrFieldsRight( $_fieldChave ) ), $scriptTableRight );

			$scriptTableRight 	= str_replace( "{complement}", $tableRight->getComplementTable ( ) , $scriptTableRight );

			$scriptSqlAssociations .= $scriptTableRight."\n";
		}

		$scriptFull = $scriptSqlAssociations.$scriptSqlCreate;

		if ( $this->TableLeft->getConfig('drop_table') === true ) {
			$scriptFull = $this->TableLeft->getDropTable() . ";\n" . $scriptFull;
		}

		return $scriptFull;
	}

	private function getArrFieldsRight( String $tagName='' ) : array {
		$arrCampos 		= [];
		$jsonArray 		= $this->getFileJsonToArray()[$tagName];
		$paramsField  	= (object) Configure::read( 'params_fields' );

		foreach( $jsonArray as $chave => $valorChave ) {

			if ( in_array( $chave, $this->config['ignore'] ) ) {
				continue;
			}

			$field = Inflector::underscore( $chave );

			if ( strlen( $field ) >= $paramsField->max_width ) {

				$field = Inflector::limitWord( $field, $paramsField->max_width_word, $paramsField->first_word_complete );
			}

			$field = substr( $field, 0, $paramsField->max_width );

			if ( in_array( gettype($valorChave), ['array'] ) ) {

				continue;
			}

			$arrCampos[] = $field;
		}

		return $arrCampos;
	}
	
	private function getArrFields( ) : array {
		$arrCampos 		= [];
		$jsonArray 		= $this->getFileJsonToArray();
		$paramsField  	= (object) Configure::read( 'params_fields' );

		foreach( $jsonArray as $chave => $valorChave ) {

			if ( in_array( $chave, $this->config['ignore'] ) ) {
				continue;
			}

			$field = Inflector::underscore( $chave );

			if ( strlen( $field ) >= $paramsField->max_width ) {

				$field = Inflector::limitWord( $field, $paramsField->max_width_word, $paramsField->first_word_complete );
			}

			$field = substr( $field, 0, $paramsField->max_width );

			if ( in_array( gettype($valorChave), ['array'] ) ) {

				$this->TableLeft->setAssociation( $field, $chave );
			}

			$arrCampos[] = $field;
		}

		return $arrCampos;
	}

}