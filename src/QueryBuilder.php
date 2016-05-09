<?php

namespace Buxus\Bql;

use FluentPDO;
use FluentStructure;
use PDO;

/**
 * Táto trieda rozširuje triedu FluentPDO knižnice „fpdo/fluentpdo“
 * a poskytuje Fluent rozhranie pre vyskladávanie dopytov.
 * @package Buxus\Bql
 */
class QueryBuilder extends FluentPDO
{
    function __construct(PDO $pdo = null)
    {
        $this->pdo = $pdo;
        $structure = new FluentStructure;
        $this->structure = $structure;
    }

    /**
     * Zakladna metoda pouzivana pri vyskladavani dopytu cez Fluent interface
     * pre SELECT dopyt
     *
     * @param $table : Tabuľka (typ stránky) z ktorého sa vyberá
     * @return BqlQuery : Inštancia triedy poskytujuca dalsie metody pre skladanie dopytu
     */
    public function from($table)
    {
        $query = new BqlQuery($this, $table);

        return $query;
    }
}