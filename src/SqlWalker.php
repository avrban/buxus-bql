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

/**
 * Trieda spracuvavajuca rozparsovany BQL dopyt do SQL dopytu
 *
 * @package Buxus\Bql
 */
class SqlWalker
{

    /**
     * Pole obsahujuce rozparsovany BQL dopyt (vystup z PHPSQLParser)
     * @var array
     */
    private $query;
    /**
     * Vysledny SQL dopyt
     * @var SqlQuery
     */
    private $sqlQuery;
    /**
     * Zoznam typov stránok CMS Buxus vyskytujúcich sa v dopyte (v dopyte ako tabulky)
     * @var array[\Buxus\Bql\PageType]
     */
    private $pageTypes = [];
    /**
     * Pocet vnorenych dopytov v danom dopyte
     * @var int
     */
    private $subqueriesCount = 0;
    /**
     * Aktualne poradove cislo vnoreneho dopytu (vyuzivane v prefixoch aliasov)
     * @var int
     */
    private $subqueryNo = 0;

    /**
     * SqlWalker constructor.
     *
     * @param $query : rozparsovany BQL dopyt
     * @param $subqueryNo : poradove cislo vnoreneho dopytu
     */
    public function __construct($query, $subqueryNo)
    {
        $this->query = $query;
        $this->sqlQuery = new SqlQuery();
        $this->subqueryNo = $subqueryNo;
    }

    /**
     * Metoda sluziaca na ziskanie vysledneho SQL dopytu
     * @return string : vysledny SQL dopyt
     */
    public function getSQL()
    {
        $this->walkFromClause();
        $this->walkSelectClause();
        $this->walkWhereClause();
        $this->walkGroupByClause();
        $this->walkHavingClause();
        $this->walkOrderByClause();
        $this->walkLimitClause();
        $this->walkPageTypes();

        return $this->sqlQuery->getSQL();
    }

    public function getPropertyFromExpr($expr)
    {
        if (count($expr["no_quotes"]["parts"]) == 3) {
            $propertyPageTypeAlias = $expr["no_quotes"]["parts"][0];
            $propertyTag = $expr["no_quotes"]["parts"][1];
            $propertyColumn = $expr["no_quotes"]["parts"][2];
        } else if (count($expr["no_quotes"]["parts"]) == 2) {
            $propertyPageTypeAlias = $expr["no_quotes"]["parts"][0];
            $propertyTag = $expr["no_quotes"]["parts"][1];

            if ($propertyTag == "to_page_id" || $propertyTag == "url" || $propertyTag == "text" || $propertyTag == "order_index") {
                $propertyPageTypeAlias = "";
                $propertyTag = $expr["no_quotes"]["parts"][0];
                $propertyColumn = $expr["no_quotes"]["parts"][1];
            }
        } else {
            $propertyPageTypeAlias = "";
            $propertyTag = $expr["base_expr"];
        }


        $propertyPageType = $this->getPageTypeByAlias($propertyPageTypeAlias);
        if ($propertyPageType == null) return null;

        $property = $propertyPageType->getPropertyByTagOrAlias($propertyTag);

        if ($property == null && empty($propertyColumn)) {
            if($expr["expr_type"]=="alias") $propertyIsAlias=true;
            $property = $propertyPageType->addProperty($propertyTag, "");
        }
        if($property==null) return null;

        if ($propertyColumn) $property->column = $propertyColumn;
        return $property;
    }

    /**
     * Metoda na ziskanie typu stranky vyskytujuceho sa v dopyte podľa jeho tagu
     *
     * @param $pageTypeTag
     * @return \Buxus\Bql\PageType
     */
    public function getPageTypeByTag($pageTypeTag)
    {
        if (!empty($this->pageTypes)) {
            foreach ($this->pageTypes as $curPageType) {
                if ($curPageType->tag == $pageTypeTag) {
                    return $curPageType;
                }
            }
        }
        return null;
    }

    /**
     * Metoda na ziskanie typu stranky vyskytujuceho sa v dopyte podla jeho pouzivatelsky definovaneho aliasu
     *
     * @param $pageTypeAlias
     * @return \Buxus\Bql\PageType
     */
    public function getPageTypeByAlias($pageTypeAlias)
    {
        if (!empty($this->pageTypes)) {
            foreach ($this->pageTypes as $curPageType) {
                if ($curPageType->alias == $pageTypeAlias) {
                    return $curPageType;
                }
            }
        }
        return null;
    }

    public function getPageTypeByTagOrAlias($pageTypeParam)
    {
        if (!empty($this->pageTypes)) {
            foreach ($this->pageTypes as $curPageType) {
                if ($curPageType->alias == $pageTypeParam || $curPageType->tag == $pageTypeParam) {
                    return $curPageType;
                }
            }
        }
        return null;
    }

    /**
     * Metoda na pridanie noveho typu stranky vyskytujuceho sa v dopyte
     *
     * @param $pageTypeTag : tag typu stranky
     * @param $pageTypeAlias : pouzivatelsky definovany alias typu stranky
     * @return \Buxus\Bql\PageType : instancia reprezentujuca dany typ stranky
     */
    public function addPageType($pageTypeTag, $pageTypeAlias)
    {
        $pm = new PageType\PageTypesManager(); //manazer typov stranok CMS Buxus

        if ($pm->pageTypeExistsByTag($pageTypeTag)) {
            //ak sa jedna o typ stranky CMS Buxus, pridame ho do zoznamu
            $pageTypeId = $pm->getPageTypeByTag($pageTypeTag)->getId();
            $pageType = new \Buxus\Bql\PageType($pageTypeId, $pageTypeTag, $pageTypeAlias, "p" . $this->subqueryNo . "_" . $pageTypeAlias);
        } else {
            //TODO: vyhodime exception alebo ho budeme brat ako fyzicku tabulku?
            // $pageType = new \Buxus\Bql\PageType(null,$pageTypeTag, $pageTypeAlias, $pageTypeAlias,true);
        }

        array_push($this->pageTypes, $pageType); //vlozenie typu stranky do zoznamu

        return $pageType;
    }

    /**
     * Metoda prechadzajuca vsetky typy stranok a ich vlastnosti vyskytujuce sa v dopyte
     * Pre kazdu vlastnost sa do dopytu pridava JOIN tabulky hodnot vlastnosti (tblPagePropertyValues)
     *
     */
    public function walkPageTypes()
    {
        if (!empty($this->pageTypes)) {
            //prechadzanie vsetkymi typmi stranok v dopyte
            foreach ($this->pageTypes as $curPageType) {
                $propertiesWhereClause = "";
                $propertiesHavingClause="";

                //prechadzanie vsetkymi vlastnostami vyskytujucimi sa v dopyte k danemu typu stranky
                if (!empty($curPageType->properties)) {
                    foreach ($curPageType->properties as $curProperty) {
                        if (!$curProperty->isColumn) {
                            if ($curProperty->class_id == C_ppc_Extended) {
                                /*v pripade, ze sa nejedna o fyzicky stlpec databazy, do klauzuly FROM vysledneho dopytu
                                sa prida tabulka hodnot vlastnosti, aby bolo mozne s nou dalej v dopyte pracovat*/
                                $this->sqlQuery->fromClause .= " JOIN tblPagePropertyValues ppv_" . $curProperty->pageTypeAlias .
                                    " ON ( ppv_" . $curProperty->pageTypeAlias . ".page_id = " . $curPageType->aliasPrefix . ".page_id" .
                                    " AND ppv_" . $curProperty->pageTypeAlias . ".property_id = " . $curProperty->id .
                                    " )";
                            } else if ($curProperty->class_id == C_ppc_Link_multivalue) {
                                /*linked properties*/
                                $this->sqlQuery->fromClause .= " JOIN tblLinkProperties lp_" . $curProperty->pageTypeAlias .
                                    " ON ( lp_" . $curProperty->pageTypeAlias . ".from_page_id = " . $curPageType->aliasPrefix . ".page_id" .
                                    " AND lp_" . $curProperty->pageTypeAlias . ".property_id = " . $curProperty->id .
                                    " )";

                                                                $this->sqlQuery->fromClause .= " JOIN tblPages lpp_" . $curProperty->pageTypeAlias .
                                                                    " ON ( lp_" . $curProperty->pageTypeAlias . ".to_page_id = lpp_" . $curProperty->pageTypeAlias . ".page_id" .
                                                                    " )";
                            }
                        }

                        if (!empty($curProperty->whereClause)) {
                            $propertiesWhereClause .= " (" . $curProperty->whereClause . ")";
                        }

                        if (!empty($curProperty->havingClause)) {
                            $propertiesHavingClause .= " (" . $curProperty->havingClause . ")";
                        }

                    }
                    if (!empty($propertiesWhereClause)) {
                        if(!empty( $this->sqlQuery->whereClause)) $this->sqlQuery->whereClause .= " AND";
                        $this->sqlQuery->whereClause .= " (" . $propertiesWhereClause . ")";
                    }
                    if (!empty($propertiesHavingClause)) {
                        if(!empty( $this->sqlQuery->havingClause)) $this->sqlQuery->havingClause .= " AND";
                        $this->sqlQuery->havingClause .= " (" . $propertiesHavingClause . ")";
                    }
                }
            }
        }
    }


    /**
     * Metoda prechadzajuca klauzulou SELECT vstupneho BQL dopytu generujuca vyslednu SELECT klauzulu SQL dopytu
     *
     * - nahradza nazvy vyberanych vlastnosti / stlpcov ich fyzickymi nazvami pouzivanymi v db
     * - podporuje pouzivatelsky definovane aliasy a taktiez agregacne funkcie
     *
     * Priklad SELECT klauzuly v BQL:
     * "SELECT page_name AS produkt, eshop_eur_price_without_vat AS cena FROM eshop_product"
     * - vyberie nazov a cenu pre vsetky produkty v eshope
     * - "eshop_eur_price_without_vat" je tag vlastnosti reprezentujucej cenu v CMS Buxus
     * - "page_name" je fyzicky stlpec v tabulke stranok reprezentujuci nazov stranky
     * - "eshop_product" je tag typu stranky reprezentujuci produkt v CMS Buxus
     */
    public function walkSelectClause()
    {
        $selectArray = $this->query["SELECT"];

        //prechadzanie SELECT klauzulou
        foreach ($selectArray as $selectPart) {
            if ($selectPart["expr_type"] == "colref" || $selectPart["expr_type"] == "aggregate_function") {

                /*zistenie tagu a aliasu vlastnosti (stlpca)*/
                if ($selectPart["expr_type"] == "colref") {
                    //jedna sa o stlpec

                    if (count($selectPart["no_quotes"]["parts"]) > 1) {
                        //nazov obsahuje aj referenciu na tabulku, ku ktorej stlpec patri
                        $propertyPageTypeAlias = $selectPart["no_quotes"]["parts"][0];
                        $propertyTag = $selectPart["no_quotes"]["parts"][1];
                    } else {
                        //nazov bez referencie tabulky
                        $propertyPageTypeAlias = "";
                        $propertyTag = $selectPart["base_expr"];
                    }
                } else if ($selectPart["expr_type"] == "aggregate_function") {
                    //jedna sa o agregacnu funkciu

                    if (count($selectPart["sub_tree"][0]["no_quotes"]["parts"]) > 1) {
                        //nazov obsahuje aj referenciu na tabulku, ku ktorej stlpec patri
                        $propertyPageTypeAlias = $selectPart["sub_tree"][0]["no_quotes"]["parts"][0];
                        $propertyTag = $selectPart["sub_tree"][0]["no_quotes"]["parts"][1];
                    } else {
                        //nazov bez referencie tabulky
                        $propertyPageTypeAlias = "";
                        $propertyTag = $selectPart["sub_tree"][0]["base_expr"];
                    }
                }

                /*nacitanie/ulozenie vlastnosti (stlpca) zo/do zoznamu*/
                $propertyPageType = $this->getPageTypeByTagOrAlias($propertyPageTypeAlias); //zistenie typu stranky (tabulky) ku ktoremu stlpec (vlastnost) patri
                if ($propertyPageType == null) {
                    //TODO: co ak neexistuje?
                    continue;
                }

                $property = $propertyPageType->getPropertyByTag($propertyTag); //vyber danej vlastnosti (stlpcu) podla tagu

                if ($property == null) {
                    //vlastnost (stlpec) este nebola v dopyte pouzita
                    if($selectPart["expr_type"]!="aggregate_function") $propertyAlias = $selectPart["alias"]["name"]; //pouzivatelsky definovany alias vlastnosti (stlpca)
                    else $propertyAlias="";

                    $property = $propertyPageType->addProperty($propertyTag, $propertyAlias); //pridanie danej vlastnosti (stlpca) do zoznamu patriacemu danej stranke (tabulke)
                }

                if (!empty($this->sqlQuery->selectClause)) $this->sqlQuery->selectClause .= ",";

                /*zistenie aliasu vlastnosti, podla ktoreho bude moct pouzivatel k stlpcu pristupovat*/
                if($selectPart["expr_type"]=="aggregate_function"){
                    if($selectPart["alias"]["name"]) $propertyFullAlias = $selectPart["alias"]["name"]; //pouzivatelsky definovany alias vlastnosti (stlpca)
                    else $propertyFullAlias = $selectPart["base_expr"] . "(" . $selectPart["sub_tree"][0]["base_expr"] . ")"; //generovany alias pre agregacnu funkciu
                }
                else {
                    if ($propertyAlias) $propertyFullAlias = $property->alias; //pouzivatelsky definovany alias
                    else $propertyFullAlias = $selectPart["base_expr"];
                } //generovany alias podla tagu vlastnosti

                /*generovanie SELECT klauzuly vysledneho SQL dopytu*/
                if ($selectPart["expr_type"] == "aggregate_function") {
                    //jedna sa o agregacnu funkciu
                    if ($property->isColumn) {
                        //fyzicky stlpec tabulky stranok
                        if ($property->tag == "*") $this->sqlQuery->selectClause .= " " . $selectPart["base_expr"] . "(" . $property->tag . ") AS '" . $propertyFullAlias . "'";
                        else $this->sqlQuery->selectClause .= " " . $selectPart["base_expr"] . "(" . $property->pageTypeAlias . "." . $property->tag . ") AS '" . $propertyFullAlias . "'";
                    } else {
                        if ($property->class_id == C_ppc_Extended) {
                            //vlastnost stranky
                            $this->sqlQuery->selectClause .= " " . $selectPart["base_expr"] . "(ppv_" . $property->pageTypeAlias . ".property_value) AS '" . $propertyFullAlias . "'";
                        }
                        if ($property->class_id == C_ppc_Link_multivalue) {
                            //vlastnost stranky
                            $this->sqlQuery->selectClause .= " " . $selectPart["base_expr"] . "(lp_" . $property->pageTypeAlias . ".property_id) AS '" . $propertyFullAlias . "'";
                        }
                    }

                } else {
                    if ($property->isColumn) {
                        //fyzicky stlpec tabulky stranok
                        if ($property->tag == "*") $this->sqlQuery->selectClause .= " " . $property->tag;
                        else $this->sqlQuery->selectClause .= " " . $property->pageTypeAlias . "." . $property->tag . " AS '" . $propertyFullAlias . "'";
                    } else {
                        if ($property->class_id == C_ppc_Extended) {
                            //vlastnost stranky
                            $this->sqlQuery->selectClause .= " ppv_" . $property->pageTypeAlias . ".property_value AS '" . $propertyFullAlias . "'";
                        } else if ($property->class_id == C_ppc_Link_multivalue) {
                            //linked property
                            $this->sqlQuery->selectClause .= " lp_" . $property->pageTypeAlias . ".to_page_id AS '" . $propertyFullAlias . ".to_page_id', lp_" . $property->pageTypeAlias . ".url AS '" . $propertyFullAlias . ".url', lp_" . $property->pageTypeAlias . ".text AS '" . $propertyFullAlias . ".text', lp_" . $property->pageTypeAlias . ".order_index AS '" . $propertyFullAlias . ".order_index', lpp_" . $property->pageTypeAlias . ".page_name AS '" . $propertyFullAlias . ".page_name', lpp_" . $property->pageTypeAlias . ".page_tag AS '" . $propertyFullAlias . ".page_tag', lpp_" . $property->pageTypeAlias . ".page_type_id AS '" . $propertyFullAlias . ".page_type_id'";
                        }

                    }
                }

            }
        }
    }

    /**
     * Metoda prechadzajuca klauzulou FROM vstupneho BQL dopytu
     *
     * Vstupny BQL dopyt v klauzule FROM obsahuje tagy typov stranok, z ktorych chce vyberat
     * Metoda overuje ich existenciu a pridava ich do zoznamu, pricom modifikuje vyslednu FROM a WHERE klauzulu
     *
     * - podporuje spajanie viacerych tabuliek (JOIN)
     * - podporuje pouzivatelsky definovane aliasy
     *
     * Priklad FROM klauzuly v BQL:
     * "SELECT ep.page_name AS produkt, ec.page_name AS kategoria
     *  FROM eshop_product ep JOIN eshop_category ec ON ec.page_id = ep.parent_page_id"
     * - pre kazdy produkt v eshope vyberie jeho nazov a nazov jeho kategorie
     * - "eshop_product" je tag typu stranky reprezentujuci produkt v CMS Buxus
     * - "eshop_category" je tag typu stranky reprezentujuci kategoriu produktov v CMS Buxus
     * - spaja sa na zaklade fyzickeho stlpca "parent_page_id" reprezentujuceho identifikator rodicovskej stranky
     */
    public function walkFromClause()
    {
        $fromArray = $this->query["FROM"];

        $ptm = new PageType\PageTypesManager(); //manazer typov stranok CMS Buxus

        //prechadzanie FROM klauzulou
        foreach ($fromArray as $fromPart) {
            if ($fromPart["expr_type"] == "table") {
                $pageTypeTag = $fromPart["table"]; //tag typu stranky (tabulky)
                $pageTypeAlias = $fromPart["alias"]["name"]; //pouzivatelsky definovany alias typu stranky (tabulky)

                $pageType = $this->addPageType($pageTypeTag, $pageTypeAlias); //pridanie typu stranky (tabulky) do zoznamu

                if (empty($this->sqlQuery->fromClause)) {
                    //prva tabulka vo FROM
                    $this->sqlQuery->fromClause = " tblPages " . $pageType->aliasPrefix; //pridanie typu stranky do vyslednej FROM klauzuly aj s jej aliasom
                } else {
                    //dalsia tabulka vo FROM => spajanie tabuliek (JOIN)

                    $this->sqlQuery->fromClause .= " " . $fromPart["join_type"] . " tblPages " . $pageType->aliasPrefix . " " . $fromPart["ref_type"]; //pridanie typu stranky do vyslednej FROM klauzuly s JOINom a aliasom
                    foreach ($fromPart["ref_clause"] as $refClause) {
                        //prechadzanie podmienkami spajania tabuliek
                        if ($refClause["expr_type"] == "colref") {
                            //jedna sa o vlastnost / stlpec
                            $property=$this->getPropertyFromExpr($refClause);
                            if($property==null) $this->sqlQuery->fromClause .= " " . $refClause["base_expr"];

                            if ($property->isColumn) {
                                //jedna sa o fyzicky stlpec a nie o vlastnost, pridame ho do FROM klauzuly tak ako je
                                $this->sqlQuery->fromClause .= " " . $property->pageTypeAlias . "." . $property->tag;
                            } else {
                                if ($property->class_id == C_ppc_Extended) {
                                    //jedna sa o vlastnost, pridame ju do FROM klauzuly s jej prefixom
                                    $this->sqlQuery->fromClause .= " ppv_" . $property->pageTypeAlias . "." . $property->tag;
                                }
                                else if ($property->class_id == C_ppc_Link_multivalue) {
                                    //jedna sa o link vlastnost
                                    $property->whereClause .= " lp_" . $property->pageTypeAlias . "." . $property->column;
                                }
                            }

                        } else {
                            //nejedna sa o vlastnost ani stlpec, do vyslednej FROM klauzuly pridavame tak ako je vo vstupe
                            $this->sqlQuery->fromClause .= " " . $refClause["base_expr"];
                        }
                    }

                }

                if (!empty($this->sqlQuery->whereClause)) $this->sqlQuery->whereClause .= " AND";
                else $this->sqlQuery->whereClause = " (";

                //pridanie typu stranky (tabulky) do vyslednej WHERE klauzuly (vybera sa z tabulky stranok (tblPages) podla identifikatora jej typu (tblPageTypes)
                $this->sqlQuery->whereClause .= " " . $pageType->aliasPrefix . ".page_type_id = " . $pageType->id;
            }
        }

        $this->sqlQuery->whereClause .= " )";
    }


    /**
     * Metoda prechadzajuca klauzulou WHERE vstupneho BQL dopytu
     *
     * Zistuje pouzite vlastnosti / stlpce v klauzule a meni ich nazvy, nasledne sklada vyslednu WHERE klauzulu SQL dopytu
     *
     * - podporuje subqueries
     *
     * Priklad pouzitia:
     * "SELECT eshop_eur_price_without_vat AS cena FROM eshop_product WHERE eshop_eur_price_without_vat>(SELECT AVG(eshop_eur_price_without_vat) FROM eshop_product)"
     * - vyberie produkty, ktorych cena je vyssia ako priemerna cena vsetkych produktov
     */
    public function walkWhereClause()
    {
        $whereArray = $this->query["WHERE"];

        if (!empty($whereArray)) {
            $lastProperty = null;

            //prechadzanie WHERE klauzuly
            foreach ($whereArray as $wherePart) {

                if ($wherePart["expr_type"] == "colref" || $wherePart["expr_type"] == "alias") {

                    $property = $this->getPropertyFromExpr($wherePart);
                    if ($property == null) {
                        continue;
                    }   

                    /*pridanie do where klauzuly*/
                    if ($property->isColumn) {
                        //jedna sa len o alias
                        if($property->isAlias){
                            $property->whereClause .= " " . $property->tag;
                        }
                        else {
                            //jedna sa o fyzicky stlpec tabulky tblPages
                            $property->whereClause .= " " . $property->pageTypeAlias . "." . $property->tag;
                        }
                    } else {
                        if ($property->class_id == C_ppc_Extended) {
                            //jedna sa o vlastnost
                            $property->whereClause .= " ppv_" . $property->pageTypeAlias . ".property_value ";

                        } else if ($property->class_id == C_ppc_Link_multivalue) {
                            //jedna sa o vlastnost
                            $property->whereClause .= " lp_" . $property->pageTypeAlias . "." . $property->column;
                        }
                    }
                    $lastProperty = $property;
                } else if ($lastProperty != null) {
                    //spracovanie subquery
                    if ($wherePart["expr_type"] == "subquery") {
                        //rekurzivne sa vola SqlWalker pre danu subquery, vysledok sa pouzije vo WHERE klauzule
                        $subQueryWalker = new SqlWalker($wherePart["sub_tree"], ++$this->subqueriesCount);
                        $lastProperty->whereClause .= " (" . $subQueryWalker->getSQL() . ")";
                    } //nejedna sa o vlastnost / stlpec ani o subquery, dame do WHERE klauzuly bezozmeny
                    else {
                        if (preg_match('/[\W]+/',$wherePart["base_expr"])==0) $lastProperty->whereClause .= " ";
                        $lastProperty->whereClause .= $wherePart["base_expr"];
                    }
                }

            }
        }
    }

    public function walkHavingClause()
    {
        $havingArray = $this->query["HAVING"];

        if (!empty($havingArray)) {
            $lastProperty = null;

            //prechadzanie having klauzuly
            foreach ($havingArray as $havingPart) {

                if ($havingPart["expr_type"] == "colref" || $havingPart["expr_type"] == "alias") {

                    $property = $this->getPropertyFromExpr($havingPart);
                    if ($property == null) {
                        continue;
                    }

                    /*pridanie do having klauzuly*/
                    if ($property->isColumn) {
                        //jedna sa len o alias
                        if($property->isAlias){
                            $property->havingClause .= " " . $property->tag;
                        }
                        else {
                            //jedna sa o fyzicky stlpec tabulky tblPages
                            $property->havingClause .= " " . $property->pageTypeAlias . "." . $property->tag;
                        }
                    } else {
                        if ($property->class_id == C_ppc_Extended) {
                            //jedna sa o vlastnost
                            $property->havingClause .= " ppv_" . $property->pageTypeAlias . ".property_value ";

                        } else if ($property->class_id == C_ppc_Link_multivalue) {
                            //jedna sa o vlastnost
                            $property->havingClause .= " lp_" . $property->pageTypeAlias . "." . $property->column;
                        }
                    }
                    $lastProperty = $property;
                } else if ($lastProperty != null) {
                    //spracovanie subquery
                    if ($havingPart["expr_type"] == "subquery") {
                        //rekurzivne sa vola SqlWalker pre danu subquery, vysledok sa pouzije vo having klauzule
                        $subQueryWalker = new SqlWalker($havingPart["sub_tree"], ++$this->subqueriesCount);
                        $lastProperty->havingClause .= " (" . $subQueryWalker->getSQL() . ")";
                    } //nejedna sa o vlastnost / stlpec ani o subquery, dame do having klauzuly bezozmeny
                    else {
                        if (preg_match('/[\W]+/',$havingPart["base_expr"])==0) $lastProperty->havingClause .= " ";
                        $lastProperty->havingClause .= $havingPart["base_expr"];
                    }
                }

            }
        }
    }

    /**
     * Metoda prechadzajuca ORDER BY klauzulu vstupneho BQL dopytu
     *
     * Zistuje pouzite vlastnosti / stlpce v klauzule a meni ich nazvy
     *
     * Priklad pouzitia:
     * "SELECT page_name AS produkt, eshop_eur_price_without_vat AS cena FROM eshop_product
     *  ORDER BY ehsop_eur_price_without_vat"
     * - vyberie nazov a cenu pre vsetky produkty, pricom ich zoradi podla ceny
     *
     * TODO: co s ciselnymi hodnotami? - cisla zoraduje ako string
     */
    public function walkOrderByClause()
    {
        $orderByArray = $this->query["ORDER"];

        if (!empty($orderByArray)) {
            //prechadzanie order by klauzluou
            foreach ($orderByArray as $orderByPart) {
                if ($orderByPart["expr_type"] != "colref" && $orderByPart["expr_type"] != "alias") continue;
                $property = $this->getPropertyFromExpr($orderByPart);
                if ($property == null) continue;

                if (!empty($this->sqlQuery->orderByClause)) $this->sqlQuery->orderByClause .= ",";

                /*pridanie do ORDER BY klauzuly*/
                if ($property->isColumn) {
                    //jedna sa len o alias
                    if($property->isAlias){
                        $this->sqlQuery->orderByClause .= " " . $property->tag;
                    }
                    else {
                        //jedna sa o fyzicky stlpec tabulky tblPages
                        $this->sqlQuery->orderByClause .= " " . $property->pageTypeAlias . "." . $property->tag;
                    }
                } else {
                    if ($property->class_id == C_ppc_Extended) {
                        //jedna sa o vlastnost
                        $this->sqlQuery->orderByClause .= " ppv_" . $property->pageTypeAlias . ".property_value +0";
                    } else if ($property->class_id == C_ppc_Link_multivalue) {
                        //jedna sa o linked vlastnost
                        if(empty($property->column)) $property->column="property_id";
                        $this->sqlQuery->orderByClause .= " lp_" . $property->pageTypeAlias . "." . $property->column." +0";
                    }
                }

                $this->sqlQuery->orderByClause .= " " . $orderByPart["direction"]; //typ zoradenia (ASC/DESC)
            }

        }

    }

    public function walkGroupByClause()
    {
        $groupByArray = $this->query["GROUP"];

        if (!empty($groupByArray)) {
            //prechadzanie group by klauzluou
            foreach ($groupByArray as $groupByPart) {

                if ($groupByPart["expr_type"] != "colref" && $groupByPart["expr_type"] != "alias") continue;

                /*zistenie vlastnosti (stlpca)*/
                $property = $this->getPropertyFromExpr($groupByPart);
                if ($property == null) continue;

                if (!empty($this->sqlQuery->groupByClause)) $this->sqlQuery->groupByClause .= ",";

                /*pridanie do GROUP BY klauzuly*/
                if ($property->isColumn) {
                    //jedna sa len o alias
                    if($property->isAlias){
                        $this->sqlQuery->groupByClause .= " " . $property->tag;
                    }
                    else {
                        //jedna sa o fyzicky stlpec tabulky tblPages
                        $this->sqlQuery->groupByClause .= " " . $property->pageTypeAlias . "." . $property->tag;
                    }
                } else {
                    if ($property->class_id == C_ppc_Extended) {
                        //jedna sa o vlastnost
                        $this->sqlQuery->groupByClause .= " ppv_" . $property->pageTypeAlias . ".property_value";
                    } else if ($property->class_id == C_ppc_Link_multivalue) {
                        //jedna sa o vlastnost
                        $this->sqlQuery->groupByClause .= " lp_" . $property->pageTypeAlias . "." . $property->column;
                    }
                }
            }
        }
    }

    /**
     * Metoda prechadzajuca LIMIT klauzulu vstupneho BQL dopytu
     * - podporuje offset (vynechanie prvych x zaznamov) aj rowcount (maximalny pocet zaznamov na vybratie)
     *
     * Priklad pouzitia:
     * "SELECT * FROM eshop_product ORDER BY ehsop_eur_price_without_vat LIMIT 5, 10"
     * - vyberie najlacnejsie produkty eshopu, pricom prvych 5 preskoci a vyberie 10 nasledujucich
     */
    public function walkLimitClause()
    {
        $limitArray = $this->query["LIMIT"];

        if (!empty($limitArray)) {
            if (!empty($limitArray["offset"])) $this->sqlQuery->limitClause .= " " . $limitArray["offset"] . ",";
            $this->sqlQuery->limitClause .= " " . $limitArray["rowcount"];
        }
    }


}

?>