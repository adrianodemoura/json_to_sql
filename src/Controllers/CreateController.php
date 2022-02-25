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

		$ConfigDb 	= (object) Configure::read( 'database' );
		$script 	= "mysql -u{$ConfigDb->user} -p{$ConfigDb->password} {$ConfigDb->database} < " . DIR_APP . "/storage/tmp/sql/socios.sql";

		$exe = exec( $script );

		dump( $exe );

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

		$DefaultField  		= (object) Configure::read( 'params_fields' );

		$config = [ 'table_name'=> str_replace( ".json", "", $this->params[1] ), 'default_string_width'=>$DefaultField->default_string_width ];

		foreach( $this->params as $_chave => $_valor ) {

			if ( strpos( $_valor, '--driver=' ) > -1 ) {
				$config['driver'] = str_replace( ['--driver=', '--driver', ' -d ', ' --d ', '=', ' '], '', $_valor );
			}

			if ( strpos( $_valor, '--prefix-table-name=' ) > -1 ) {
				$config['prefix_table_name'] = str_replace( ['--prefix-table-name=', '=', ' '], '', $_valor );
			}
		}

		$this->TableSchemaLeft = new TableSchema( $config );
	}

	private function getScriptSqlCreate() : string {

		$DefaultField  		= (object) Configure::read( 'params_fields' );

		$tableLeft 			=  $this->TableSchemaLeft->getConfig('prefix_table_name') . $this->TableSchemaLeft->getConfig('table_name');

		$scriptSqlCreate 	= "\nCREATE TABLE {$tableLeft} (\n{campos}) {complement};\n";

		$scriptSqlCreate 	= str_replace( "{campos}", 	$this->TableSchemaLeft->getFields( $this->getArrFieldsLeft() ), $scriptSqlCreate );

		$scriptSqlCreate 	= str_replace( "{complement}", $this->TableSchemaLeft->getComplementTable ( ) , $scriptSqlCreate );

		$scriptSqlAssociations = '';

		foreach( $this->TableSchemaLeft->getAssociation() as $_fieldAssociation => $_fieldChave ) {

			$tableNameRight		= Inflector::pluralize( Inflector::underscore( $_fieldChave ) );

			$prefixTableLeft 	= $this->TableSchemaLeft->getConfig('prefix_table_name');

			$TableSchemaRight	= new TableSchema( [ 'table_name'=>$tableNameRight, 'driver'=> $this->TableSchemaLeft->getConfig('driver'), 'prefix_table_name'=>$prefixTableLeft, 'default_string_width'=>$DefaultField->default_string_width ] );

			$scriptTableRight 	= ( $TableSchemaRight->getConfig('drop_table') === true ) ? $TableSchemaRight->getDropTable().';' : '';

			$scriptTableRight   .= "\nCREATE TABLE {$prefixTableLeft}{$tableNameRight} (\n{campos}\n) {complement};\n";

			$scriptTableRight 	= str_replace( "{campos}", $TableSchemaRight->getFields( $this->getArrFieldsRight( $_fieldChave ) ), $scriptTableRight );

			$scriptTableRight 	= str_replace( "{complement}", $TableSchemaRight->getComplementTable ( ) , $scriptTableRight );

			$scriptSqlAssociations .= $scriptTableRight."\n";
		}

		$scriptFull = $scriptSqlAssociations.$scriptSqlCreate;

		if ( $this->TableSchemaLeft->getConfig('drop_table') === true ) {
			$scriptFull = $this->TableSchemaLeft->getDropTable() . ";\n" . $scriptFull;
		}

		return $scriptFull;
	}

	private function getArrFieldsLeft( ) : array {
		$arrCampos 		= [];

		$jsonArray 		= $this->getFileJsonToArray();

		$DefaultField  	= (object) Configure::read( 'params_fields' );

		foreach( $jsonArray as $chave => $valorChave ) {

			if ( in_array( $chave, $this->config['ignore'] ) ) {
				continue;
			}

			$field = Inflector::underscore( $chave );

			if ( strlen( $field ) >= $DefaultField->max_width ) {

				$field = Inflector::limitWord( $field, $DefaultField->max_width_word, $DefaultField->first_word_complete );
			}

			$field = substr( $field, 0, $DefaultField->max_width );

			if ( in_array( gettype($valorChave), ['array'] ) ) {

				$this->TableSchemaLeft->setAssociation( $field, $chave );
			}

			$arrCampos[] = $field;
		}

		return $arrCampos;
	}

	private function getArrFieldsRight( String $tagName='' ) : array {
		$arrCampos 		= [];

		$jsonArray 		= $this->getFileJsonToArray()[$tagName];

		$DefaultField  	= (object) Configure::read( 'params_fields' );

		foreach( $jsonArray as $chave => $valorChave ) {

			if ( in_array( $chave, $this->config['ignore'] ) ) {
				continue;
			}

			$field = Inflector::underscore( $chave );

			if ( strlen( $field ) >= $DefaultField->max_width ) {

				$field = Inflector::limitWord( $field, $DefaultField->max_width_word, $DefaultField->first_word_complete );
			}

			$field = substr( $field, 0, $DefaultField->max_width );

			if ( in_array( gettype($valorChave), ['array'] ) ) {

				continue;
			}

			$arrCampos[] = $field;
		}

		return $arrCampos;
	}

}