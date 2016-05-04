--TEST--
jednoduchy select tabulky prostrednictvom BQL
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/*TODO: riesit subcategories?*/
$BQLquery="SELECT ep.page_name AS produkt, ec.page_name AS kategoria FROM eshop_product ep JOIN eshop_category ec ON ec.page_id = ep.parent_page_id";
$SQLquery=$bql->getSQL($query);

print_r($pdo->query(SQLquery));

?>
--EXPECTF--
