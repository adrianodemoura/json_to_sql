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

		$this->populate( $this->params[1], (int) $this->params[2] );

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

	private function populate( String $tableName='', Int $quantity=0 ) : Bool {
		
		$describe 	= $this->Mysql->describeTable( $this->params[1] );

		$arrSqls  	= [];

		for( $i=0; $i<$quantity; $i++ ) {

			$sql = "INSERT INTO " . $this->params[1] . " SET ";

			$l = 0;
			foreach( $describe as $_field => $_arrProp ) {


				if ( isset( $_arrProp['extra'] ) && $_arrProp['extra'] == 'auto_increment' ) {
					continue;
				}

				if ( isset( $_arrProp['default'] ) && !empty( $_arrProp['default'] ) ) {
					continue;
				}

				if ( isset( $_arrProp['key'] ) &&  $_arrProp['key'] == 'MUL' ) {
					continue;
				}

				if ( $l > 0 ) { $sql .= ", "; }

				$sql .= $_field . " = " . $this->getFakeValue( $_arrProp, ($i+1) );

				$l++;
			}

			$arrSqls[] = $sql;
		}

		$this->Mysql->begin();

		try {
			foreach( $arrSqls as $_key => $_sql ) {

				$this->Mysql->query( $_sql );
			}

			$this->Mysql->commit();
		} catch (Exception $e) {

			$this->Mysql->rollback( $e->getMessage() );

			throw new Exception( $e->getMessage() );
		}

		return true;
	}
}