<?php
/**
 * Created by PhpStorm.
 * User: avrban
 * Date: 09.03.2016
 * Time: 18:02
 */

namespace Buxus\Bql;


/**
 * Trieda reprezentujuca vysledny SQL dopyt
 * @package Buxus\Bql
 */
class SqlQuery
{
    /**
     * Cast dopytu vztahujuca sa k prikazu SELECT
     * @var string
     */
    public $selectClause;
    /**
     * Cast dopytu vztahujuca sa k prikazu FROM
     * @var string
     */
    public $fromClause;
    /**
     * Cast dopytu vztahujuca sa k prikazu WHERE
     * @var string
     */
    public $whereClause;
    /**
     * Cast dopytu vztahujuca sa k prikazu GROUP BY
     * @var string
     */
    public $groupByClause;
    /**
     * Cast dopytu vztahujuca sa k prikazu HAVING
     * @var string
     */
    public $havingClause;
    /**
     * Cast dopytu vztahujuca sa k prikazu ORDER BY
     * @var string
     */
    public $orderByClause;
    /**
     * Cast dopytu vztahujuca sa k prikazu LIMIT
     * @var string
     */
    public $limitClause;

    /**
     * Metoda spajajuca jednotlive casti dopytu dokopy
     *
     * @return string : vysledny SQL dopyt
     */
    public function getSQL()
    {
        $query = "SELECT" . $this->selectClause;
        if (!empty($this->fromClause)) $query .= " FROM" . $this->fromClause;
        if (!empty($this->whereClause)) $query .= " WHERE" . $this->whereClause;
        if (!empty($this->groupByClause)) $query .= " GROUP BY" . $this->groupByClause;
        if (!empty($this->havingClause)) $query .= " HAVING" . $this->havingClause;
        if (!empty($this->orderByClause)) $query .= " ORDER BY" . $this->orderByClause;
        if (!empty($this->limitClause)) $query .= " LIMIT" . $this->limitClause;
        return $query;
    }
}