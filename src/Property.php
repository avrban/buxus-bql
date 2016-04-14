<?php
/**Created by PhpStorm.
 * User: avrban
 * Date: 19.03.2016
 * Time: 17:26
 */

namespace Buxus\Bql;


/**
 * Trieda reprezentujuca vlastnosti stranok vyskytujuce sa v dopyte (v dopyte sa vyskytuju ako stlpce).
 * Kazda vlastnost patri k niektoremu typu stranky.
 *
 * @package Buxus\Bql
 */
class Property
{
    /**
     * Identifikátor vlastnosti stránky (z DB "property_id" v tabuľke tblProperties)
     * V prípade, že sa jedná o fyzický stĺpec a nie vlastnost, je identifikátor prázdny
     * @var integer
     */
    public $id;
    /**
     * Tag vlastnosti stranky (z DB "property_tag" v tabulke tblProperties)
     * @var string
     */
    public $tag;
    /**
     * Alias (s prefixom) typu stranky, ku ktorej vlastnost patri
     * @var string
     */
    public $pageTypeAlias;
    /**
     * Pouzivatelsky zvoleny alias vlastnosti pouzivany v dopyte
     * @var string
     */
    public $alias;
    /**
     * Cast vysledneho dopytu k prikazu WHERE, vztahujuca sa k danej vlastnosti
     * @var string
     */
    public $whereClause;
    /**
     * Logicka premenna oznacujuca, ci sa jedna o fyzicky stlpec tabulky alebo vlastnost stranky
     * @var boolean
     */
    public $isColumn;

    /**
     * Property constructor.
     * @param $id
     * @param $tag
     * @param $pageTypeAlias
     * @param $alias
     * @param $isColumn
     */
    public function __construct($id, $tag, $pageTypeAlias, $alias, $isColumn)
    {
        $this->id=$id;
        $this->tag=$tag;
        $this->pageTypeAlias=$pageTypeAlias;
        $this->alias=$alias;
        $this->isColumn=$isColumn;
    }
}
?>
