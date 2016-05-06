<?php
/**
 * Created by PhpStorm.
 * User: avrban
 * Date: 05.04.2016
 * Time: 22:48
 */

namespace Buxus\Bql;

use FluentPDO;
use FluentStructure;
use PDO;

class QueryBuilder extends FluentPDO
{
    function __construct(PDO $pdo=null) {
        $this->pdo = $pdo;

            $structure = new FluentStructure;
        $this->structure = $structure;
    }

    public function from($table) {
        $query = new BqlQuery($this, $table);

        return $query;
    }
}