<?php
declare(strict_types=1);

namespace App\Database\Driver;
use PDO;
use PDOException;

class MysqlPdo extends PDO {

	/**
	 * Resultado da última query.
	 *
	 * @var 	mixed
	 */
	protected $result 		= null;

	/**
	 * contador de transcações
	 *
	 * @var 	int
	 */
	protected $transactionCounter = 0;

	/**
	 * Caminho e nome do log
	 */
	private $nameLog 		= '';

	/**
	 */
	private $lastaTypeSql 	= '';

	/**
	 */
	private $dirLog 		= 'sql';

	/**
	 */
	private $beginStart 	= false;

	/**
	 */
	private $configDb 		= [];

	/**
	 * Método start
	 *
	 * @return 	void
	 */
	public function __construct( Array $configDb = [] ) {

		$dsn = "mysql:host=" . $configDb['host'] . ";dbname=" . $configDb['database'];

		$configDb['flags'] = isset( $configDb['flags'] ) ? $configDb['flags'] : [];

		$this->configDb = $configDb;

		parent::__construct( $dsn, $configDb['username'], $configDb['password'], $configDb['flags'] );

		$this->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	}

	public function __destruct( ) {

	}

	/**
	 * Inicia a transação do banco.
	 *
	 * @return void
	 */
	public function begin() {
		//if ( $this->nameLog ) { gravaLog( date("Y-m-d H:i:s") . " BEGIN", ( !empty($this->dirLog)?$this->dirLog.'/':'' ) . 'sql_'.$this->nameLog, 'a+' ); }
		$this->beginStart = true;

		return ( !$this->transactionCounter++ ) ? parent::beginTransaction() : $this->transactionCounter >= 0;
	}

	/**
	 * Executa o commit do banco.
	 *
	 * @return mixed
	 */
	public function commit() {
		if ( $this->nameLog ) {

        	$nameFile 	= $this->nameLog;

			$dirLogSql = !empty($this->dirLog) ? $this->dirLog.'/' :'';

        	$nameFile 	.= (! empty($this->lastaTypeSql) ) ? '_'.$this->lastaTypeSql : '';

			gravaLog( date("Y-m-d H:i:s") . " COMMIT", $dirLogSql . 'sql_'.$nameFile, 'a+' );
		}

		$this->beginStart = false;

		return (!--$this->transactionCounter) ? parent::commit() : $this->transactionCounter >= 0;
	}

	/**
	 * Executa o rollback do banco.
	 *
	 * @return mixed
	 */
	public function rollback( String $erro='' ) {

        if ( $this->nameLog ) {
        	
        	$dirLogSql = !empty($this->dirLog) ? $this->dirLog.'/' :'';

        	$nameFile 	= $this->nameLog;

        	$nameFile 	.= (! empty($this->lastaTypeSql) ) ? '_'.$this->lastaTypeSql : '';

        	gravaLog( date("Y-m-d H:i:s") . " ROLLBACK" . (! empty($erro)? " ( $erro )" : '' ) , $dirLogSql . 'sql_'.$nameFile, 'a+' );
        }

        $this->beginStart = false;

		if ( $this->transactionCounter >= 0 )
        {
            $this->transactionCounter = 0;
            return parent::rollback();
        }

        $this->transactionCounter = 0;
        return false;
	}

	public function exists( String $tableName='' ) {
		$sql = "SELECT COUNT(*) as totalReg FROM information_schema.tables WHERE table_schema = '".$this->configDb['database']."' AND table_name = '{$tableName}'";

		if ( @parent::query( $sql )->fetchAll( PDO::FETCH_ASSOC )[0]['totalReg'] ) {

			return true;
		}

		return false;
	}

	/**
	 * Configura o nome do log
	 * 
	 * @var string
	 */
	public function setLogName( string $nameLog='' ) {

		if ( strpos( $nameLog, "/") ) {
			$arrNameLog = explode( "/", $nameLog );

			$this->dirLog 	= $arrNameLog[0] . "/";

			$this->nameLog = $arrNameLog[1];
		} else {

			$this->nameLog = $nameLog;
		}
	}

	/**
	 * Execute a query
	 * 
	 * @param 	string 	$query 	Query a ser executada.
	 */
	public function query( string $query='' ) {

		if ( $this->nameLog ) {

			$nameFile = $this->nameLog;

			$dirLogSql = !empty($this->dirLog) ? $this->dirLog.'/' :'';

			if ( substr($query,0,6) == 'UPDATE' )   { $nameFile = "sql_".$this->nameLog."_update"; $this->lastaTypeSql='update'; }
			if ( substr($query,0,6) == 'INSERT' )   { $nameFile = "sql_".$this->nameLog."_insert"; $this->lastaTypeSql='insert'; }
			if ( substr($query,0,6) == 'DELETE' )   { $nameFile = "sql_".$this->nameLog."_delete"; $this->lastaTypeSql='delete'; }
			if ( substr($query,0,8) == 'TRUNCATE' ) { $nameFile = "sql_".$this->nameLog."_truncate"; $this->lastaTypeSql='truncate'; }
			if ( substr($query,0,8) == 'DESCRIBE' ) { $nameFile = "sql_".$this->nameLog."_describe"; $this->lastaTypeSql='describe'; }

			if ( $this->beginStart ) {
				gravaLog( "-------------------------", $dirLogSql . $nameFile, 'a+' );
				gravaLog( date("Y-m-d H:i:s") . " BEGIN", $dirLogSql . $nameFile, 'a+' );
				gravaLog( " ", $dirLogSql . $nameFile, 'a+' );
				$this->beginStart = false;
			}

			gravaLog( date("Y-m-d H:i:s") . " " . $query, $dirLogSql . $nameFile, 'a+' );
			gravaLog( " ", $dirLogSql . $nameFile, 'a+' );
		}

		$this->result = parent::query( $query );

		return $this;
	}

	/**
	 * Retorna o resultado de uma query
	 *
	 * @return 	array 	array 	Resultado da query em array;
	 */
	public function toArray() : array {

		return @$this->result->fetchAll( PDO::FETCH_ASSOC );
	}

	/**
	 * Retorna a lista de todas as tabels do banco de dados.
	 *
	 * @return 	array $listTables 	Lista das tabelas.
	 */
	public function allTables() : array {

		return parent::query( "SHOW TABLES" )->fetchAll( PDO::FETCH_COLUMN );
	}

	/**
	 * Retorna a as propriedades de cada campo de uma tabela.
	 *
	 * @param 	string 	$db 		Origem do banco, source ou target.
	 * @param 	string 	$table 		Nome da tabela
	 * @return 	array 	$fields 	Matriz com todos as propriedades dde cada campo.
	 */
	public function describeTable ( string $table='' ) : array {

		$_listFields 	= parent::query( "DESCRIBE $table" )->fetchAll( PDO::FETCH_ASSOC );

		$fields 		= [];

		foreach( $_listFields as $_l => $_arrProp )
		{
			$fieldName = @$_arrProp['Field'];

			$fields[ $fieldName ]['name'] 	= $fieldName;
			$fields[ $fieldName ]['type'] 	= @$_arrProp['Type'];
			$fields[ $fieldName ]['null'] 	= @$_arrProp['Null'];
			$fields[ $fieldName ]['key'] 	= @$_arrProp['Key'];
			$fields[ $fieldName ]['default']= @$_arrProp['Default'];
			$fields[ $fieldName ]['extra']  = @$_arrProp['Extra'];

			if ( $fields[ $fieldName ]['key'] == 'MUL' ) {

				$_propAssoc = parent::query( "SELECT TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME 
					FROM information_schema.KEY_COLUMN_USAGE 
					WHERE 
					    TABLE_NAME   = '{$table}'
					AND COLUMN_NAME  = '{$fieldName}'
					AND REFERENCED_TABLE_NAME IS NOT NULL;" )
				->fetchAll( PDO::FETCH_ASSOC );

				$fields[ $fieldName ]['referenced_table_name']  = @$_propAssoc[0]['REFERENCED_TABLE_NAME'];
				$fields[ $fieldName ]['referenced_column_name'] = @$_propAssoc[0]['REFERENCED_COLUMN_NAME'];
			}
		}

		return $fields;
	}
}