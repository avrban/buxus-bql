<?php
namespace Buxus\Bql;

/**
 * Trieda pre spustanie interpretera BQL jazyka prostrednictvom konzoly cez PHP command
 *
 * @package Buxus\Bql
 */
class Main
{
    /**
     * Metoda urcena na spustanie konverzie z BQL do SQL, vysledny SQL dopyt sa vypise
     *
     * @param $query : BQL dopyt
     */
    function execute($query)
    {
        $bq = new Bql();
        echo $bq->getSQL($query, true) . "\n";
    }
}