<?php
namespace Buxus\Bql;

class Main
{
    /**
     * Metoda urcena na spustanie konverzie z BQL do SQL, vysledny SQL dopyt sa vypise
     *
     * @param $query : BQL dopyt
     */
    function execute($query){
        $bq = new Bql();
        echo $bq->getSQL($query,true)."\n";

       /*$qb=new QueryBuilder();
        echo $qb->from('eshop_product')->where('eshop_eur_price_without_vat >',100)->getSQL();*/
    }
}
?>