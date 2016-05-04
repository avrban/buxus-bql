--TEST--
select s filtrovanim podla vnoreneho dopytu
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";

/*TODO: bude toto fungovat?*/

$BQLsubquery=$qb->from('eshop_product')->select('AVG(eshop_eur_price_without_vat)')->getBQL();
$SQLquery=$qb->from('eshop_product')->select('page_name AS produkt, eshop_eur_price_without_vat AS cena')->where('eshop_eur_price_without_vat >',$BQLsubquery);

print_r($pdo->query(SQLquery));

?>
--EXPECTF--


