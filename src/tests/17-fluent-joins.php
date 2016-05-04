--TEST--
jednoduchy select tabulky prostrednictvom BQL
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/*TODO: riesit subcategories? funguje smart join?*/

$SQLquery=$qb->from('eshop_product')
    ->select('eshop_product.page_name AS produkt, eshop_category.page_name AS kategoria')
    ->getSQL();

print_r($pdo->query(SQLquery));

?>
--EXPECTF--
