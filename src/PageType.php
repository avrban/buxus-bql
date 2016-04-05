<?php
/**
 * Created by PhpStorm.
 * User: avrban
 * Date: 21.03.2016
 * Time: 20:25
 */

namespace Buxus\Bql;


class PageType
{
    public $id;
    public $tag;
    public $alias;
    public $whereClause;
    public $isColumn;

    public function __construct($id,$tag,$alias,$isColumn)
    {
        $this->id=$id;
        $this->tag=$tag;
        $this->alias=$alias;
        $this->isColumn=$isColumn;
    }
}