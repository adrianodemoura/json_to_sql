<?php
declare(strict_types=1);

namespace App\Utility;

class Message {

    private static $_list = [
        '0005'  => 'Não foi possivel localizar o arquivo "{1}", certifique-se que se encontra no diretório "{2}"',
        '0006'  => 'Arquivo "{1}" gerado com sucesso em "{2}"',
        '0007'  => 'Não foi possivel escrever o arquivo "{1}" em "{2}"',
        '0008'  => 'Tabela "{1}"" populada com dados fake com sucesso.',
        '0009'  => '"Configuração {1} inválida para este Schema."',
        '0010'  => 'Tabela "{1}" inexistente.'
    ];

    public static function get ( $code, $params=[] ) : string {

        $msg = isset( static::$_list [ $code ] ) ? static::$_list [ $code ] : '??';

        foreach( $params as $_key => $_vlr ) {
            $msg = str_replace( '{'.($_key+1).'}', $_vlr, $msg );
        }

        return $msg;
    }
}