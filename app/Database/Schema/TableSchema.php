<?php
declare(strict_types=1);

namespace App\Database\Schema;

use App\Database\Schema\Traits\FieldTrait;
use App\Utility\Inflector;
use App\Utility\Message as MSG;
use Exception;

class TableSchema {

    use FieldTrait;

	private $config = [];

	private $fieldsAssociaton = [];

	public function __construct( Array $config=[] ) {

		$config['drop_table'] 			= isset( $config['drop_table'] ) 		? (bool) $config['drop_table'] 	: true;
		$config['driver'] 				= isset( $config['driver'] ) 			? $config['driver'] 			: 'mysql';
		$config['prefix_table_name'] 	= isset( $config['prefix_table_name'] ) ? $config['prefix_table_name'] 	: 'tb_';
		$config['table_name'] 			= isset( $config['table_name'] ) 		? $config['table_name'] 		: '';

		$this->config = $config;
	}

	public function getConfig( String $name='' ) {

		if (! isset( $this->config[ $name ] ) ) {

			throw new Exception( MSG::get( '0009', [ $name ] ) );
		}

		return $this->config[ $name ];
	}

	public function getComplementTable ( ) : string {
		switch ( $this->config['driver'] ) {

			case 'postgresql':
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

	public function setAssociation ( String $field='', String $chave='' ) {

		$this->fieldsAssociaton[$field] = $chave;
	}

	public function getDropTable( ) : string {
		
		switch ( $this->getConfig('driver') ) {
			case 'oracle':
				$string = "DROP TABLE ".$this->getConfig('prefix_table_name').$this->getConfig('table_name');
				break;
			
			default: // mysql, mariaDB, PostreqSql
				$string = "DROP TABLE IF EXISTS ".$this->getConfig('prefix_table_name').$this->getConfig('table_name');
				break;
		}

		return $string;
	}

	public function getAssociation () : array {

		return $this->fieldsAssociaton;
	}

	public function getDefaultDateTie() : string {
		switch ( $this->getConfig('driver') ) {
			case 'oracle':
				return "CURRENT_TIME";
				break;

			default:
				return "curtime()";
				break;
		}
	}

	private function getFields( Array $campos=[] ) : string {

		$fieldsCreate = '';

		$fieldsCreate .= "\t  id INT auto_increment NOT NULL";

		foreach( $campos as $_key => $_field ) {

			if ( in_array( $_field, $this->fieldsAssociaton) ) {
				continue;
			}

			$field 		= $this->getNameField( $_field );
			$width 		= $this->getWidth( $_field );
			$type 		= $this->getType( $_field );
			$null 		= $this->getNull( $_field );
			$default 	= $this->getDefault( $_field );

			$default 	= (! empty($default) ) 	? "DEFAULT $default" 	: '';
			$type 		= (! empty($type) ) 	? $type 				: "VARCHAR";
			$width 		= (! empty($width) ) 	? $width 				: $this->getConfig( 'default_string_width' );
			$width 		= in_array( $type, ['date','datetime'] ) ? 0 	: $width;

			$type 		= ( $width >0 ) ? "$type($width)" : $type;

			$fieldsCreate .= "\n\t, $field $type $null $default";
		}

		foreach( $this->fieldsAssociaton as $_fieldAssociation => $_chaveAssociation ) {
			
			$fieldsCreate .= "\n\t, {$_fieldAssociation}_id INT(11)";
		}

		$fieldsCreate .= "\n\t, PRIMARY KEY (id)";

		foreach( $this->fieldsAssociaton as $_fieldAssociation => $_chaveAssociation ) {
			
			$tableLeft  = Inflector::pluralize( $this->config['table_name'] );

			$tableRight = Inflector::pluralize( Inflector::underscore( $_chaveAssociation ) );

			$prefixTable= $this->config['prefix_table_name'];

			$fieldsCreate .= "\n\t, CONSTRAINT {$tableLeft}__{$tableRight}_FK FOREIGN KEY ({$_fieldAssociation}_id) REFERENCES $prefixTable{$tableRight} (id)";
		}

		return str_replace("{tableName}", strtoupper($this->getConfig('table_name')), $fieldsCreate."\n" );
	}

	private function getNameField( String $field='' ) : string {

		return substr( $field, 0, $this->getConfig( 'max_width' ) );
	}
	
}