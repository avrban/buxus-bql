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
    public function getBQL()
    {
        $queryBQL = $this->getQuery(false);
        $queryParameters = $this->getParameters();

        foreach($queryParameters as $curParam) {
            $queryBQL = str_replace('?', $curParam, $queryBQL);
        }

        echo $queryBQL."\n";

        return $queryBQL;
    }

    public function getSQL()
    {
        $parser=new PHPSQLParser();
        $parsed=$parser->parse($this->getBQL()); //pole s rozparsovanym vstupnym dopytom
        print_r($parsed);

        /*konverzia do SQL dopytu a vypis*/
        $walker=new SqlWalker($parsed,0);
        $sql=$walker->getSQL();
        echo $sql."\n";
    }
}