<?php
namespace Buxus\Bql;

use Buxus\Bql;
use PHPSQLParser\PHPSQLParser;

class Main
{
    /**
     * Metoda urcena na spustanie konverzie z BQL do SQL, vysledny SQL dopyt sa vypise
     *
     * @param $query : BQL dopyt
     */
    function execute($query){
        /*parsovanie vstupneho dopytu*/
        $parser=new PHPSQLParser();
        $parsed=$parser->parse($query); //pole s rozparsovanym vstupnym dopytom
        print_r($parsed);

        /*konverzia do SQL dopytu a vypis*/
        $walker=new SqlWalker($parsed,0);
        $sql=$walker->getSQL();
        echo $sql."\n";

       /* $qb=new QueryBuilder();
        echo $qb->from('eshop_product')->where('eshop_eur_price_without_vat >',100)->getSQL();*/
    }
}
?>