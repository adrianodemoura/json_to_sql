<?php
declare(strict_types=1);

namespace App\Database\Schema;

use App\Database\Schema\Traits\Field;

class TableSchema {

	use Field;

	private $config = [];

	public function __construct( Array $config=[] ) {

		$config['id_auto'] 		= isset( $config['id_auto'] ) 		? $config['id_auto'] 	: false;
		$config['driver'] 		= isset( $config['driver'] ) 		? $config['driver'] 	: 'mysql';
		$config['table_name'] 	= isset( $config['table_name'] ) 	? $config['table_name'] : '??';

		$this->config = $config;
	}

	public function getConfig( String $name='' ) : string {
		return $this->config[ $name ];
	}

	public function getFields( Array $campos=[] ) : string {

		$fieldsCreate = '';

		switch ( $this->getConfig('driver') ) {

			case 'postgresql':
			case 'postgres':
			case 'postgre':
				$fieldsCreate = static::getFieldsCreatePostgresql( $campos );
				break;

			case 'oracle':
				$fieldsCreate = static::getFieldsCreateOracle( $campos );
				break;

			default:
				$fieldsCreate = static::getFieldsCreateMysql( $campos );
		}

		$fieldsCreate = str_replace( "{tableName}", strtoupper( $this->getConfig('table_name') ), $fieldsCreate );

		return $fieldsCreate;
	}

	public function getComplementTable ( ) : string {
		switch ( $this->config['driver'] ) {

			case 'postgresql':
			case 'postgres':
			case 'postgre':
				return "";
				break;

			case 'oracle':
				return "";
				break;

			default:
				return "ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
		}		
	}

	private function getFieldsCreateMysql( Array $campos=[] ) : string {

		$fieldsCreate = '';

		if ( $this->config['id_auto'] === true ) {
			$fieldsCreate .= "\tid INT auto_increment NOT NULL,\n";
		}

		foreach( $campos as $_key => $_field ) {
			$field 		= $this->getNameField( $_field );
			$width 		= $this->getWidth( $_field );
			$type 		= $this->getType( $_field );
			$null 		= $this->getNull( $_field );
			$default 	= $this->getDefault( $_field );

			$default 	= (! empty($default) ) 	? "DEFAULT $default": '';
			$type 		= (! empty($type) ) 	? $type: "VARCHAR";
			$width 		= (! empty($width) ) 	? $width: 100;
			$width 		= in_array( $type, ['date','datetime'] ) ? 0 : $width;

			$type 		= ( $width >0 ) ? "$type($width)" : $type;

			$fieldsCreate .= "\t$field $type $null $default";

			if ( $_key >= 0 && $_key <= (count($campos)-2) ) {
				$fieldsCreate .= ", ";
			}

			$fieldsCreate .= "\n";
		}

		if ( $this->config['id_auto'] === true ) {
			$fieldsCreate .= "\t, CONSTRAINT ".$this->config['table_name']."_PK PRIMARY KEY (id)\n";
		}

		return $fieldsCreate;
	}

	private function getNameField( String $field='' ) : string {

		return substr( $field, 0, 30 );
	}
	
}