<?php
declare(strict_types=1);

if ( !function_exists('debug') )
{
    /**
     * Exibe na tela o backTrace
     *
     * @param   string  $d Mensagem
     * @return  void
     */
    function debug($d='', $trace=true) 
    {
        $t = array_reverse(debug_backtrace());
        $m = array();
        foreach($t as $_l => $_arrProp)
        {
            if (isset($_arrProp['line']))
            {
                $m[$_arrProp['line']] = $_arrProp['file'];
            }
        }

        $c = 0;
        foreach($m as $_l => $_file)
        {
            $c++;
            if ( !$trace ) {
                if ( $c <= count($m) ) { continue; } 
            }
            
            echo $_file.' (linha: '.$_l.')';

            if ( PHP_SAPI === 'cli' ) echo "\n";
        }
        echo print_r($d,true);
        if ( PHP_SAPI === 'cli' ) echo "\n";
    }
}

if ( !function_exists('dump') )
{
    /**
     * Exibe na tela o backTrace
     *
     * @param   string  $d Mensagem
     * @return  void
     */
    function dump( $d='' ) 
    {
        $t = array_reverse(debug_backtrace());
        $m = array();
        foreach($t as $_l => $_arrProp)
        {
            if (isset($_arrProp['line']))
            {
                $m[$_arrProp['line']] = $_arrProp['file'];
            }
        }

        echo print_r($d,true);

        if ( PHP_SAPI === 'cli' ) echo "\n";
    }
}

if ( !function_exists('dd') )
{
    /**
     * Exibe na tela o backGrace e saí.
     *
     * @param   string  $d Mensagem
     * @return  void
     */
    function dd($d='') 
    {
        debug( $d );
        die("==\n");
    }
}

if ( !function_exists('gravaLog') )
{
    /**
     * Escreve um Log no diretório temporário
     *
     * @param   string  $log        Nome do arquivo log.
     * @param   mixed   $conteudo   Conteúdo do logo.
     * @param   string  $tipo       Tipo da escrita, utiliza a+ para continuar a escrita, o padrão é re-escrever o arquivo.
     * @return  void
     */
    function gravaLog($conteudo='', $nomeLog='log', $tipo='w')
    {
        $fp = fopen( TMP . DS . $nomeLog.'.log', $tipo);

        ob_start();

        echo print_r($conteudo, true);

        $saida = ob_get_clean();
        fwrite($fp, $saida.PHP_EOL);
        fclose($fp);
    }
}

if ( !function_exists('success') )
{
    function success( string $texto='' )
    {
        echo print_r( $texto, true);
        if ( PHP_SAPI === 'cli' ) echo "\n";
    }
}

if ( !function_exists('error') )
{
    function error( string $texto='' )
    {
        echo print_r( $texto, true);
        if ( PHP_SAPI === 'cli' ) echo "\n";
    }
}