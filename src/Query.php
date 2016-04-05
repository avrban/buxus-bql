<?php
/**
 * Created by PhpStorm.
 * User: avrban
 * Date: 09.03.2016
 * Time: 18:02
 */

namespace Buxus\Bql;


class Query
{
    public $selectClause;
    public $fromClause;
    public $whereClause;
    public $groupByClause;
    public $havingClause;
    public $orderByClause;
    public $limitClause;

    public function getSQL(){
        $query="SELECT".$this->selectClause;
        if(!empty($this->fromClause)) $query.=" FROM".$this->fromClause;
        if(!empty($this->whereClause)) $query.=" WHERE".$this->whereClause;
        if(!empty($this->groupByClause)) $query.=" GROUP BY".$this->gropuByClause;
        if(!empty($this->havingClause)) $query.=" HAVING".$this->havingClause;
        if(!empty($this->orderByClause)) $query.=" ORDER BY".$this->orderByClause;
        if(!empty($this->limitClause)) $query.=" LIMIT".$this->limitClause;
        return $query;
    }
}
?>