<?php
/**
 * Created by PhpStorm.
 * User: avrban
 * Date: 09.03.2016
 * Time: 18:40
 */

namespace Buxus\Bql;

use Buxus\PageType;
use Buxus\Page;
use Buxus\Property;
use Buxus\Bql;

class SqlWalker
{

    private $query;
    private $sqlQuery;
    private $properties=[];
    private $aliasPrefix;
    private $subqueriesCount=0;

    public function __construct($query,$aliasPrefix)
    {
        $this->query = $query;
        $this->aliasPrefix=$aliasPrefix;
        $this->sqlQuery=new Query();
    }

    public function getSQL(){
        $this->walkSelectClause();
        $this->walkFromClause();
        $this->walkWhereClause();
        $this->walkHavingClause();
        $this->walkGroupByClause();
        $this->walkOrderByClause();
        $this->walkLimitClause();
        $this->walkProperties();

        return $this->sqlQuery->getSQL();

    }

    public function getPropertyByTag($propertyTag){
        if (!empty($this->properties)) {
            foreach ($this->properties as $curProperty) {
                if ($curProperty->tag == $propertyTag) {
                   return $curProperty;
                }
            }
        }
        return null;
    }

    public function addProperty($propertyTag){
        $pm = new Property\PropertyManager();

        if($pm->propertyExistsByTag($propertyTag)) {
            $propertyId = $pm->getPropertyByTag($propertyTag)->getId();
            $property = new \Buxus\Bql\Property($propertyId, $propertyTag, "ppv".$this->aliasPrefix."_" . (count($this->properties)),false);
        }
        else $property = new \Buxus\Bql\Property(null,$propertyTag,null,true);

        array_push($this->properties, $property);

        return $property;
    }

    public function walkProperties(){
        $propertiesWhereClause="";

        if (!empty($this->properties)) {
            foreach ($this->properties as $curProperty) {
                if(!$curProperty->isColumn) {
                    $this->sqlQuery->fromClause .= " JOIN tblPagePropertyValues " . $curProperty->alias .
                        " ON ( " . $curProperty->alias . ".page_id = p".$this->aliasPrefix.".page_id" .
                        " AND " . $curProperty->alias . ".property_id = " . $curProperty->id .
                        " )";
                }

                if(!empty($curProperty->whereClause)){
                    $propertiesWhereClause .= " (" . $curProperty->whereClause .")";
                }

            }
            if(!empty($propertiesWhereClause)) $this->sqlQuery->whereClause.=" AND (".$propertiesWhereClause.")";
        }
    }

    public function walkSelectClause()
    {
        $this->sqlQuery->fromClause = " tblPages p".$this->aliasPrefix;

        $selectArray = $this->query["SELECT"];

        foreach ($selectArray as $selectPart) {
            if ($selectPart["expr_type"] == "colref" || $selectPart["expr_type"] == "aggregate_function") {

                if($selectPart["expr_type"] == "colref") $propertyTag = $selectPart["base_expr"];
                else if($selectPart["expr_type"]=="aggregate_function") $propertyTag=$selectPart["sub_tree"][0]["base_expr"];

                if($propertyTag=="*") {
                    $this->sqlQuery->selectClause.=" *";
                    continue;
                }

                $property=$this->getPropertyByTag($propertyTag);

                if ($property == null) {
                   $property=$this->addProperty($propertyTag);
                }


                if(!empty($this->sqlQuery->selectClause)) $this->sqlQuery->selectClause.=",";

                if($selectPart["expr_type"]=="aggregate_function") {
                    if($property->isColumn){
                        $this->sqlQuery->selectClause.=" ".$selectPart["base_expr"]."(".$property->tag.")";
                    }
                    else {
                        $this->sqlQuery->selectClause.=" ".$selectPart["base_expr"]."(" . $property->alias . ".property_value) AS '" . $selectPart["base_expr"]."(".$property->tag.")'";
                    }

                }
                else {
                    if($property->isColumn) {
                        $this->sqlQuery->selectClause .= " " . $property->tag;
                    }
                    else {
                        $this->sqlQuery->selectClause .= " " . $property->alias . ".property_value AS '" . $property->tag."'";
                    }
                }

            }
        }
    }

    //TODO SPAJANIE TABULIEK
    public function walkFromClause(){
        $fromArray=$this->query["FROM"];

        $ptm=new PageType\PageTypesManager();

        foreach($fromArray as $fromPart){
            if($fromPart["expr_type"]=="table"){
                $pageTypeTag=$fromPart["table"];

                $pageTypeId=$ptm->getPageTypeByTag("$pageTypeTag")->getId();
                if(!empty($this->sqlQuery->whereClause)) $this->sqlQuery->whereClause.=" OR";
                else $this->sqlQuery->whereClause=" (";

                $this->sqlQuery->whereClause.=" p".$this->aliasPrefix.".page_type_id = ".$pageTypeId;
            }
        }

        $this->sqlQuery->whereClause.=" )";
    }

    //TODO subquery
    public function walkWhereClause(){
        $whereArray=$this->query["WHERE"];

        if(!empty($whereArray)) {
            $lastProperty=null;
            foreach ($whereArray as $wherePart) {
                if ($wherePart["expr_type"] == "colref") {
                    $propertyTag = $wherePart["base_expr"];

                    $property=$this->getPropertyByTag($propertyTag);
                    if ($property == null) {
                        $property=$this->addProperty($propertyTag);
                    }

                    if(!$property->isColumn) {
                        $property->whereClause .= " ".$property->alias.".property_value";
                    }
                    else {
                        $property->whereClause .= " ".$property->tag;
                    }

                    $lastProperty=$property;

                }

                else if($wherePart["expr_type"]=="subquery"){
                    $subQueryWalker=new SqlWalker($wherePart["sub_tree"],++$this->subqueriesCount);
                    $lastProperty->whereClause.=" (".$subQueryWalker->getSQL().")";
                }

                else {
                    $lastProperty->whereClause .= " " . $wherePart["base_expr"];
                }

            }
        }
    }


    public function walkHavingClause(){
        //echo $this->query->havingClause;
    }

    //TODO co s ciselnymi hodnotami?
    public function walkOrderByClause(){
        $orderByArray=$this->query["ORDER"];

        if(!empty($orderByArray)) {
            foreach ($orderByArray as $orderByPart) {
                if ($orderByPart["expr_type"] == "colref") {
                    $propertyTag = $orderByPart["base_expr"];

                    $property = $this->getPropertyByTag($propertyTag);
                    if ($property == null) {
                        $property = $this->addProperty($propertyTag);
                    }

                    if(!empty($this->sqlQuery->orderByClause)) $this->sqlQuery->orderByClause.=",";

                    if (!$property->isColumn) {
                        $this->sqlQuery->orderByClause .= " " . $property->alias . ".property_value";
                    } else {
                        $this->sqlQuery->orderByClause .= " " . $property->tag;
                    }

                    $this->sqlQuery->orderByClause .= " ".$orderByPart["direction"];
                }
            }
        }
    }

    public function walkGroupByClause(){
        //echo $this->query->groupByClause;
    }

    public function walkLimitClause(){
        $limitArray=$this->query["LIMIT"];

        if(!empty($limitArray)) {
            if (!empty($limitArray["offset"])) $this->sqlQuery->limitClause .= " " . $limitArray["offset"].",";
            $this->sqlQuery->limitClause .= " " . $limitArray["rowcount"];
        }
    }


}
?>