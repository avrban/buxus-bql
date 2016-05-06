<?php
/**
 * Created by PhpStorm.
 * User: avrban
 * Date: 01.05.2016
 * Time: 12:46
 */

namespace Buxus\Bql;

use PHPSQLParser\PHPSQLParser;

class BqlQuery extends \SelectQuery
{
    public function getBQL($debug=false)
    {
        $this->disableSmartJoin();
        $queryBQL = $this->getQuery(false);
        $queryParameters = $this->getParameters();

        foreach($queryParameters as $curParam) {
            $queryBQL = str_replace('?', $curParam, $queryBQL);
        }

        if($debug) echo $queryBQL."\n";

        return $queryBQL;
    }

    public function getSQL($debug=false)
    {
        $parser=new PHPSQLParser();
        $parsed=$parser->parse($this->getBQL()); //pole s rozparsovanym vstupnym dopytom
        if($debug) print_r($parsed);

        /*konverzia do SQL dopytu a vypis*/
        $walker=new SqlWalker($parsed,0);
        $sql=$walker->getSQL($debug);

        if($debug) echo $sql."\n";;
        return $sql;
    }
}