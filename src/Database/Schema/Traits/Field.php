<?php
declare(strict_types=1);

namespace App\Database\Schema\Traits;

trait Field {

	private $_fields = [
		'ativo'				=> [ 'type'=>'int', 		'width'=>1, 'default'=>1, 'null'=>'NOT NULL'  ],
		'codigo' 			=> [ 'type'=>'int', 		'width'=>11 ],
		'cpf' 				=> [ 'type'=>'int', 		'width'=>11 ],
		'nire' 				=> [ 'type'=>'int', 		'width'=>11 ],
		'cnpj' 				=> [ 'type'=>'int', 		'width'=>14 ],
		'aniversario' 		=> [ 'type'=>'int', 		'width'=>14 ],

		'celular' 			=> [ 'type'=>'int', 		'width'=>13 ], //55 31 91234-4321
		'telefone' 			=> [ 'type'=>'int', 		'width'=>12 ], //55 31 1234-4321
		
		'data_nascimento' 	=> [ 'type'=>'date', 		'width'=>10 ],
		'data_criacao' 		=> [ 'type'=>'datetime', 	'width'=>18 ],
		'data_modificao'	=> [ 'type'=>'datetime', 	'width'=>18 ],
	];

	public function getType( String $field='' ) {
		return isset( $this->_fields[ $field ]['type'] ) ? $this->_fields[ $field ]['type'] : '';
	}

	public function getWidth( String $field='' ) {
		return isset( $this->_fields[ $field ]['width'] ) ? $this->_fields[ $field ]['width'] : '';
	}

	public function getDefault( String $field='' ) {
		return isset( $this->_fields[ $field ]['default'] ) ? $this->_fields[ $field ]['default'] : '';
	}

	public function getNull( String $field='' ) : string {
		return isset( $this->_fields[ $field ]['null'] ) ? $this->_fields[ $field ]['null'] : 'NULL';
	}

}
