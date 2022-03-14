<?php
declare(strict_types=1);

namespace App\Database\Schema\Traits;

trait FieldTrait {

	private $_fields = [
		'ativo'				=> [ 'type'=>'int', 		'width'=>1, 'default'=>1, 'null'=>'NOT NULL'  ],
		'aniversario' 		=> [ 'type'=>'int', 		'width'=>4  ],
		'codigo' 			=> [ 'type'=>'int', 		'width'=>11 ],
		'nire' 				=> [ 'type'=>'float', 		'width'=>11, 'min'=>10000000000, 'max'=>99999999999 ],
		'cpf' 				=> [ 'type'=>'float', 		'width'=>11, 'min'=>10000000000, 'max'=>99999999999 ],
		'cnpj' 				=> [ 'type'=>'float', 		'width'=>14, 'min'=>10000000000000, 'max'=>99999999999999 ],

		'celular' 			=> [ 'type'=>'int', 		'width'=>13 ], //55 31 91234-4321
		'telefone' 			=> [ 'type'=>'int', 		'width'=>12 ], //55 31 1234-4321
		
		'data_nascimento' 	=> [ 'type'=>'date', 		'width'=>10 ],
		'data_saida' 		=> [ 'type'=>'date', 		'width'=>10 ],
		'data_entrada' 		=> [ 'type'=>'date', 		'width'=>10 ],
		'data_inicio'		=> [ 'type'=>'date', 		'width'=>10 ],
		'data_termino'		=> [ 'type'=>'date', 		'width'=>10 ],

		'data_criacao' 		=> [ 'type'=>'datetime', 	'width'=>18 ],
		'data_modificao'	=> [ 'type'=>'datetime', 	'width'=>18 ],
		'criado_em'			=> [ 'type'=>'datetime', 	'width'=>18 ],
		'modificado_em'		=> [ 'type'=>'datetime', 	'width'=>18 ],
	];

	public function getType( String $field='' ) {

		return isset( $this->_fields[ $field ]['type'] ) ? $this->_fields[ $field ]['type'] : '';
	}

	public function getWidth( String $field='' ) {

		return isset( $this->_fields[ $field ]['width'] ) ? $this->_fields[ $field ]['width'] : '';
	}

	public function getDefault( String $field='' ) {

		if ( in_array($field, ['criado_em', 'modificado_em', 'created_at', 'update_at'] ) ) {

			return $this->getDefaultDateTie();
		}

		return isset( $this->_fields[ $field ]['default'] ) 
			? $this->_fields[ $field ]['default'] 
			: '';
	}

	public function getNull( String $field='' ) : string {

		return isset( $this->_fields[ $field ]['null'] ) ? $this->_fields[ $field ]['null'] : 'NULL';
	}

	public function getFakeValue( Array $propField=[], Int $count=0 ) {

		if ( in_array($propField['name'], ['criado_em', 'modificado_em', 'created_at', 'update_at'] ) ) {

			return;
		}

		$typeField = $propField['type'];

		if ( strpos( $typeField, "(" ) > -1 ) {

			$typeField = substr( $typeField, 0, strpos( $typeField, "(" ) );
		}


		if ( in_array( $typeField, ['date'] ) ) {

			$date = strtotime( '-'. rand( 0, 80 ) .' year' );

			return "'" . date('Y-m-d', $date ) . "'";
		}

		if ( in_array( $typeField, ['datetime', 'timestamp'] ) ) {

			return "'". date('Y-m-d H:i:s') . "'";
		}

		if ( in_array( strtolower($typeField), [ 'int', 'float', 'double', 'number', 'numeric' ] ) ) {

			$a = 1000;
			$b = 1100;

			if ( isset( $this->_fields[ $propField['name'] ]['min'] ) ) {

				$a = $this->_fields[ $propField['name'] ]['min'];
			}

			if ( isset( $this->_fields[ $propField['name'] ]['max'] ) ) {

				$b = $this->_fields[ $propField['name'] ]['max'];
			}

			return rand( $a, $b );
		}

		if ( strpos( $typeField, 'varchar' ) > -1 ) {

			$width = (int) str_replace( [ 'varchar2', 'varchar(', ')' ], '', $propField['type'] );

			$fakeValue = $propField['name'].' '. ( ($count)?$count.' ':'' );

			$value = "'" . substr( str_repeat(  $fakeValue, $width ), 0, ($width-2) ) . "'";

			return $value;
		}

		return $propField['type'];
	}

}
