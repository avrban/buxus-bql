<?php
/**
 * Created by PhpStorm.
 * User: avrban
 * Date: 01.05.2016
 * Time: 12:46
 */

namespace Buxus\Bql;

use PHPSQLParser\PHPSQLParser;

/**
 * Trieda rozširuje triedu SelectQuery knižnice „fpdo/fluentpdo".
 * Obsahuje metódu getBQL(), ktorá konvertuje dopyt vyskladaný cez Fluent rozhranie do klasického zápisu,
 * pričom nahrádza jednotlivé parametre dopytu ich skutočnými hodnotami.
 * Prítomná je tiež metóda getSQL() pre získanie výsledného SQL dopytu.
 * @package Buxus\Bql
 */
class BqlQuery extends \SelectQuery
{
    /**
     * Metoda na ziskanie BQL dopytu v klasickom zapise z dopytu vyskladaneho prostrednictvom Fluent interface
     * Nahradza parametre ich skutocnymi hodnotami
     * @param $debug : Zobrazovat debugovacie vypisy
     * @return string : BQL dopyt v klasickom zapise
     */
    public function getBQL($debug = false)
    {
        $this->disableSmartJoin();
        $queryBQL = $this->getQuery(false);
        $queryParameters = $this->getParameters();

        foreach ($queryParameters as $curParam) {
            $queryBQL = str_replace('?', $curParam, $queryBQL);
        }

        if ($debug) echo $queryBQL . "\n";

        return $queryBQL;
    }

    /**
     * Metoda na ziskanie vysledneho SQL dopytu z dopytu vyskladaneho prostrednictvom Fluent interface
     * @param $debug : Zobrazovat debugovacie vypisy
     * @return string : Vysledny SQL dopyt
     */
    public function getSQL($debug = false)
    {
        $parser = new PHPSQLParser();
        $parsed = $parser->parse($this->getBQL()); //pole s rozparsovanym vstupnym dopytom
        if ($debug) print_r($parsed);

        /*konverzia do SQL dopytu a vypis*/
        $walker = new SqlWalker($parsed, 0);
        $sql = $walker->getSQL($debug);

        if ($debug) echo $sql . "\n";;
        return $sql;
    }
}