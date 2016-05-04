<?php
/**
 * Created by PhpStorm.
 * User: avrban
 * Date: 04.05.2016
 * Time: 8:23
 */

namespace Buxus\Bql;

use PHPSQLParser\PHPSQLParser;

class Bql
{
    /**
     * Metoda urcena na spustanie konverzie z BQL do SQL, vysledny SQL dopyt sa vypise
     *
     * @param $query : BQL dopyt
     */
    function getSQL($query, $debug=false){
        $parser=new PHPSQLParser();
        $parsed=$parser->parse($query); //pole s rozparsovanym vstupnym dopytom
        if($debug) print_r($parsed);

        /*konverzia do SQL dopytu a vypis*/
        $walker=new SqlWalker($parsed,0);
        $sql=$walker->getSQL();

        return $sql;
    }
}