<?php
/**
 * Created by PhpStorm.
 * User: avrban
 * Date: 19.03.2016
 * Time: 17:26
 */

namespace Buxus\Bql;


class Property
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
?>
