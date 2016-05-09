<?php
/**
 * Created by PhpStorm.
 * User: avrban
 * Date: 21.03.2016
 * Time: 20:25
 */

namespace Buxus\Bql;

use Buxus\Property;

/**
 * Trieda reprezentuje typy stranok vyskytujuce sa v spracovavanom dopyte (v dopyte sa vyskytuju ako tabulky)
 * a poskytuje metody na pridavanie a vyber vlastnosti patriacich k danemu typu stranok.
 *
 * @package Buxus\Bql
 */
class PageType
{
    /**
     * Identifikator typu stranky ("page_type_id" z DB)
     * @var integer
     */
    public $id;
    /**
     * Tag typu stranky ("page_type_tag" z DB)
     * @var string
     */
    public $tag;
    /**
     * Generovany prefix aliasu typu stranky, sluziaci na jedinecnu identifikaciu v dopyte
     * @var string
     */
    public $aliasPrefix;
    /**
     * Pouzivatelsky zvoleny alias typu stranky
     * @var string
     */
    public $alias;
    /**
     * Zoznam vlastnosti pouzitych v dopyte k danemu typu stranky
     * @var array[Buxus\Bql\Property]
     */
    public $properties = [];

    /**
     * PageType constructor.
     * @param $id
     * @param $tag
     * @param $alias
     * @param $aliasPrefix
     */
    public function __construct($id, $tag, $alias, $aliasPrefix)
    {
        $this->id = $id;
        $this->tag = $tag;
        $this->alias = $alias;
        $this->aliasPrefix = $aliasPrefix;
    }

    /**
     * Metoda sluziaca na vyhladanie vlastnosti patriacej k typu stranky podla jej tagu
     * @param $propertyTag : tag hladanej vlastnosti
     * @return \Buxus\Bql\Property : najdena vlastnost
     */
    public function getPropertyByTag($propertyTag)
    {
        if (!empty($this->properties)) {
            foreach ($this->properties as $curProperty) {
                if ($curProperty->tag == $propertyTag) {
                    return $curProperty;
                }
            }
        }
        return null;
    }

    /**
     * Metoda sluziaca na vyhladanie vlastnosti patriacej k typu stranky podla jej tagu
     * alebo pouzivatelsky definovaneho aliasu
     * @param $propertyParam : tag alebo alias hladanej vlastnosti
     * @return \Buxus\Bql\Property : najdena vlastnost
     */
    public function getPropertyByTagOrAlias($propertyParam)
    {
        if (!empty($this->properties)) {
            foreach ($this->properties as $curProperty) {
                if ($curProperty->tag == $propertyParam || $curProperty->alias == $propertyParam) {
                    return $curProperty;
                }
            }
        }
        return null;
    }


    /**
     * Metoda sluziaca na pridanie novej vlastnosti k danemu typu stranky
     *
     * @param $propertyTag : tag pridavanej vlastnosti
     * @param $propertyAlias : pouzivatelsky definovany alias pridavanej vlastnosti
     * @return \Buxus\Bql\Property : instancia reprezentujuca pridanu vlastnost
     */
    public function addProperty($propertyTag, $propertyAlias)
    {
        if (empty($propertyAlias)) $propertyAlias = $propertyTag;

        $pm = new Property\PropertyManager(); //manazer vlastnosti stranok CMS Buxus

        if ($pm->propertyExistsByTag($propertyTag)) {
            //vlastnost je definovana v CMS Buxus
            $buxusProperty = $pm->getPropertyByTag($propertyTag);

            $property = new \Buxus\Bql\Property($buxusProperty->getId(), $propertyTag, $this->aliasPrefix, $propertyAlias, false, $buxusProperty->getClassId());
        } else {
            //vlastnost nie je definovana v CMS Buxus
            if (preg_match('/(page_id|page_name|page_tag|author_id|creation_date|page_type_id|page_state_id|parent_page_id|page_class_id|last_updated|sort_date_time|properties|last_updated_by_user_id)/', $propertyTag) == 1) {
                //fyzicky stlpec tabulky tblPages
                $property = new \Buxus\Bql\Property(null, $propertyTag, $this->aliasPrefix, $propertyAlias, true, 0);
            } else {
                //alias
                $property = new \Buxus\Bql\Property(null, $propertyTag, $this->aliasPrefix, $propertyAlias, true, 0, true);
            }

        }

        array_push($this->properties, $property); //ulozenie vlastnosti do zoznamu

        return $property;
    }

}