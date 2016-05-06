<?php
/**
 * Created by PhpStorm.
 * User: avrban
 * Date: 05.04.2016
 * Time: 22:48
 */

namespace Buxus\Bql;

use FluentPDO;

class QueryBuilder extends FluentPDO
{
    function __construct() {

    }

    public function from($table) {
        $query = new BqlQuery($this, $table);

        return $query;
    }
}