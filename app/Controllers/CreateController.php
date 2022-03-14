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

	private $tableRight2 = [];

	public function execute( ) {

		$this->validate();

		$this->startTime();

		$this->setTableSchema();

		$fileOut  			= $this->params[1] . '.sql';

		$dirEscrita  		= "/storage/tmp/sql";		

		$scriptSqlCreate 	= $this->getScriptSqlCreate();

		if (! file_put_contents( DIR_APP . $dirEscrita.'/'.$fileOut, $scriptSqlCreate ) ) {

			throw new Exception( MSG::get('0007', [$fileOut, $dirEscrita ] ) );
		}

		$ConfigApp  = (object) Configure::read( 'app' );

		if ( $ConfigApp->force_create === true ) {

			$ConfigDb 	= (object) Configure::read( 'database' );

			$script 	= "mysql -u{$ConfigDb->username} -p{$ConfigDb->password} {$ConfigDb->database} < " . DIR_APP . "/storage/tmp/sql/{$this->params[1]}.sql";

			$exe = exec( $script );
		}

		$this->addTime( 'SUCESSO', MSG::get('0006', [ $fileOut, $dirEscrita ] ) );		

		$this->endTime();

		$this->printTime();
	}

	private function validate( ) {
		if ( !isset( $this->params[1] ) ) {

			throw new Exception( 'Informe o nome do arquivo JSON !' );
		}

		$this->params[1] = explode(",", $this->params[1] )[0];

		if ( !file_exists( STORAGE . "/tmp/json/" . explode(",", $this->params[1] )[0] . '.json' ) ) {

			throw new Exception( MSG::get( '0005', [ $this->params[1] . '.json' , "/tmp/json" ] ) );
		}
	}

	private function setTableSchema( ) {

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

		$config['max_width'] 		= $DefaultField->max_width;
		$config['max_width_word'] 	= $DefaultField->max_width_word;
		$config['ignore'] 			= $DefaultField->ignore;

		$this->TableSchemaLeft = new TableSchema( $config );
	}

	private function getScriptSqlCreate( ) : string {

		$tableLeft 				=  $this->TableSchemaLeft->getConfig('prefix_table_name') . $this->TableSchemaLeft->getConfig('table_name');

		$sqlDropTable 			= ( $this->TableSchemaLeft->getConfig('drop_table') === true ) ? $this->TableSchemaLeft->getDropTable().";\n\n" : "";

		$scriptSqlCreate 		= "CREATE TABLE {$tableLeft} (\n{campos}) {complement};\n";

		$scriptSqlCreate 		= str_replace( "{campos}", 	$this->TableSchemaLeft->getFields( $this->getFieldsLeft() ), $scriptSqlCreate );

		$scriptSqlCreate 		= str_replace( "{complement}", $this->TableSchemaLeft->getComplementTable ( ) , $scriptSqlCreate );

		$scriptSqlAssociations = "";

		foreach( $this->TableSchemaLeft->getAssociation() as $_fieldAssociation => $_fieldChave ) {

			$scriptSqlAssociations .= $this->getSqlCreateTableLeft( $_fieldAssociation, $_fieldChave );
		}

		foreach( $this->tableRight2 as $_chave => $_arr ) {
			$DefaultField  		= (object) Configure::read( 'params_fields' );

			$prefixTableLeft 	= $this->TableSchemaLeft->getConfig('prefix_table_name');

			$fieldName  		= str_replace( "_id", "", $_arr['field'] );

			$tableNameRight 	= $_arr['table'];

			$TableSchemaRight	= new TableSchema( [ 'table_name'=>$tableNameRight, 'driver'=> $this->TableSchemaLeft->getConfig('driver'), 'prefix_table_name'=>$prefixTableLeft, 'default_string_width'=>$DefaultField->default_string_width, 'max_width'=>$DefaultField->max_width ] );

			$sqlDropTable .= ( $TableSchemaRight->getConfig('drop_table') === true ) ? $TableSchemaRight->getDropTable().";\n\n" : "";
		}

		$scriptFull = "{$sqlDropTable}{$scriptSqlAssociations}{$scriptSqlCreate}";

		return $scriptFull;
	}

	private function getSqlCreateTableLeft( String $fieldsAssociaton='', String $fieldChave='' ) : string {

		$DefaultField  		= (object) Configure::read( 'params_fields' );

		$widthField  		= $DefaultField->default_string_width;

		$tableNameRight		= Inflector::pluralize( Inflector::underscore( $fieldChave ) );

		$prefixTableLeft 	= $this->TableSchemaLeft->getConfig('prefix_table_name');

		$TableSchemaRight	= new TableSchema( [ 'table_name'=>$tableNameRight, 'driver'=> $this->TableSchemaLeft->getConfig('driver'), 'prefix_table_name'=>$prefixTableLeft, 'default_string_width'=>$DefaultField->default_string_width, 'max_width'=>$DefaultField->max_width ] );

		$sqlDropTable 		= ( $TableSchemaRight->getConfig('drop_table') === true ) ? $TableSchemaRight->getDropTable().";\n" : "";

		$fieldsTableRight  	= $TableSchemaRight->getFields( $this->getFieldsRight( $fieldChave, $this->getFileJsonToArray()[$fieldChave] ) );

		$sqlCreateTableRight2 = "";

		if ( isset( $this->tableRight2[ $fieldChave ] ) ) {

			$this->tableRight2[ $fieldChave ]['table'] = $tableNameRight;

			$chave2  			= $this->tableRight2[ $fieldChave ]['chave'];

			$fieldName2  		= $this->tableRight2[ $fieldChave ]['field'];

			$tableNameRight2 	= str_replace( "_id", "", $fieldName2 );

			$TableSchemaRight2	= new TableSchema( [ 'table_name'=>$tableNameRight2, 'driver'=> $this->TableSchemaLeft->getConfig('driver'), 'prefix_table_name'=>$prefixTableLeft, 'default_string_width'=>$DefaultField->default_string_width, 'max_width'=>$DefaultField->max_width ] );

			$fieldsTableRight   = str_replace("{$fieldName2} VARCHAR($widthField) NULL", "{$fieldName2} INT(11) NULL", $fieldsTableRight );

			$fieldsTableRight2  = $TableSchemaRight2->getFields( $this->getFieldsRight( $fieldName2, $this->tableRight2[ $fieldChave ]['chave'] ) );

			$fieldsTableRight .= "\n\t,CONSTRAINT {$tableNameRight}__{$tableNameRight2}_FK FOREIGN KEY ({$fieldName2}) REFERENCES {$prefixTableLeft}{$tableNameRight2} (id)";

			$sqlDropTable2 		= ( $TableSchemaRight2->getConfig('drop_table') === true ) ? $TableSchemaRight2->getDropTable().";\n" : "";

			$sqlCreateTableRight2 = "{$sqlDropTable2}CREATE table {$prefixTableLeft}{$tableNameRight2} (\n{$fieldsTableRight2}\n) {$TableSchemaRight->getComplementTable( )};\n\n\n";

			$sqlDropTable 		= "";
		}

		$scriptTableRight   = "{$sqlCreateTableRight2}{$sqlDropTable}CREATE TABLE {$prefixTableLeft}{$tableNameRight} (\n{campos}\n) {complement};\n";

		$scriptTableRight 	= str_replace( "{campos}",     $fieldsTableRight, $scriptTableRight );

		$scriptTableRight 	= str_replace( "{complement}", $TableSchemaRight->getComplementTable ( ) , $scriptTableRight );

		return (! empty( $scriptTableRight ) ) ? $scriptTableRight."\n" : "";
	}

	private function getFieldsLeft( ) : array {
		$arrCampos 		= [];

		$jsonArray 		= $this->getFileJsonToArray();

		$DefaultField  	= (object) Configure::read( 'params_fields' );

		foreach( $jsonArray as $chave => $valorChave ) {

			if ( in_array( $chave, $DefaultField->ignore ) ) {

				continue;
			}

			$field = Inflector::underscore( $chave );

			if ( strlen( $field ) >= $DefaultField->max_width ) {

				$field = Inflector::limitWord( $field, $DefaultField->max_width_word, $DefaultField->first_word_complete );
			}

			$field = substr( $field, 0, $DefaultField->max_width );

			if ( in_array( gettype($valorChave), ['array'] ) ) {

				$this->TableSchemaLeft->setAssociation( $field, $chave );

				continue;
			}

			$arrCampos[] = $field;
		}

		return $arrCampos;
	}

	private function getFieldsRight( String $tagName='', Array $jsonArray=[] ) : array {

		$arrCampos 		= [];

		if ( isset( $jsonArray[0] ) ) {

			$jsonArray = $jsonArray[0];
		}

		$DefaultField  	= (object) Configure::read( 'params_fields' );

		foreach( $jsonArray as $chave => $valorChave ) {

			if ( in_array( $chave, $DefaultField->ignore ) ) {

				continue;
			}

			$field = Inflector::underscore( $chave );


			if ( strlen( $field ) >= $DefaultField->max_width ) {

				$field = Inflector::limitWord( $field, $DefaultField->max_width_word, $DefaultField->first_word_complete );
			}

			$field = substr( $field, 0, $DefaultField->max_width );

			if ( in_array( gettype($valorChave), ['array'] ) ) {

				$field .= "_id";

				$this->tableRight2[ $tagName ] = ['field'=> $field, 'chave'=> $valorChave ];
			}

			$arrCampos[] = $field;
		}

		return $arrCampos;
	}

}