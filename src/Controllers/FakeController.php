<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Controller as BaseController;
use App\Traits\TimePiece;
use App\Traits\Sql;
use App\Utility\Message as MSG;
use App\Configure\Configure;
use App\Database\Driver\MysqlPdo as Mysql;
use App\Database\Schema\Traits\FieldTrait;
use Exception;

class FakeController extends BaseController {

	use TimePiece;

	use FieldTrait;

	public function execute() {

		$this->validate();

		$this->startTime();

		$this->Mysql = new Mysql( Configure::read( 'database' ) );

		$this->Mysql->setLogName( "sql/".$this->params[1] );

		$this->Mysql->begin();

		try {

			if (! $this->Mysql->exists( $this->params[1])  ) {

				throw new Exception ( MSG::get('0010', [ $this->params[1] ] ) );
			}

			$this->populatePk( $this->params[1], (int) rand(5,10) );
			
			$this->populate( $this->params[1], (int) $this->params[2] );

			$this->Mysql->commit();
		} catch (Exception $e) {
			
			$this->Mysql->rollback( $e->getMessage() );

			throw new Exception( $e->getMessage() );
		}

		$this->addTime( 'SUCESSO', MSG::get('0008', [ $this->params[1] ] ) );

		$this->endTime();

		$this->printTime();
	}

	private function validate() {
		if (! isset( $this->params[1] ) ) {

			throw new Exception( 'Informe o nome da ' . $this->params[1] . ' a ser populada !' );
		}

		if (! isset( $this->params[2] ) ) {

			throw new Exception( 'Informe a quantidade de registros para a tabela ' . $this->params[1] . ' !' );
		}
	}

	private function populatePk ( String $tableNameIgnore='', Int $quantity=0 ) {

		$databaseName 	= @Configure::read( 'database' )['database'];

		$allTablesPk 	= $this->Mysql->query( "SELECT * FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA='{$databaseName}' AND  CONSTRAINT_TYPE = 'PRIMARY KEY'" )->toArray();

		$listTables  	= [];

		$tablesLast  	= [];

		foreach( $allTablesPk as $_l => $_arrProp ) {
			
			if ( $_arrProp['TABLE_NAME'] === $tableNameIgnore ) {

				continue;
			}

			$propriedadesTabela = $this->Mysql->describeTable( $_arrProp['TABLE_NAME'] );

			foreach( $propriedadesTabela as $_field => $_propField ) {

				if ( $_propField['key'] == 'MUL' ) {

					$tablesLast[] = $_arrProp['TABLE_NAME'];

					continue;
				}
			}

			if (! in_array( $_arrProp['TABLE_NAME'] , $tablesLast ) ) {
	
				$listTables[] = $_arrProp['TABLE_NAME'];
			}
		}

		$listTables = array_merge( $listTables, $tablesLast );

		foreach( $listTables as $_l => $_tableName ) {

			$totalTable = @$this->Mysql->query( "SELECT COUNT(1) as totalReg FROM {$_tableName}" )->toArray()[0]['totalReg'];

			if (! $totalTable ) {

				$this->populate( $_tableName, $quantity );
			}
		}
	}

	private function populate( String $tableName='', Int $quantity=0 ) {

		$describe 	= $this->Mysql->describeTable( $tableName );

		$arrSqls  	= [];

		for( $i=0; $i<$quantity; $i++ ) {

			$sql = "INSERT INTO {$tableName} SET ";

			$l = 0;
			foreach( $describe as $_field => $_arrProp ) {


				if ( isset( $_arrProp['extra'] ) && $_arrProp['extra'] == 'auto_increment' ) {

					continue;
				}

				if ( isset( $_arrProp['default'] ) && !empty( $_arrProp['default'] ) ) {

					continue;
				}

				$value = $this->getFakeValue( $_arrProp, ($i+1) );

				if ( isset( $_arrProp['key'] ) &&  $_arrProp['key'] == 'MUL' ) {

					$prefixTable = @Configure::read('database')['prefix_table_name'];
					$prefixTable = ( $prefixTable ) ? $prefixTable : '';

					$value = $this->getIdAssociation(  $_arrProp['referenced_table_name'], $_arrProp['referenced_column_name'] );
				}

				if ( $l > 0 ) { $sql .= ", "; }

				$sql .= $_field . " = " . $value;

				$l++;
			}

			$arrSqls[] = $sql;
		}

		foreach( $arrSqls as $_key => $_sql ) {

			$this->Mysql->query( $_sql );
		}
	}

	private function getIdAssociation( String $tableName, String $fieldName ) : int {

		$faixaId = $this->Mysql->query( "SELECT {$fieldName} FROM {$tableName} ORDER BY {$fieldName}" )->toArray();

		$sortKey = rand( 0, count( $faixaId )-1 );

		if (! isset( $faixaId[ $sortKey ] ) ) {
			dump( $faixaId );
			dump( $sortKey );
			dd('fudeu');
		}

		return (int) $faixaId[ $sortKey  ];
	}
}