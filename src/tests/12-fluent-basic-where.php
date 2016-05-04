--TEST--
jednoduchy select tabulky s filtrovanim
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";

$SQLquery=$qb->from('eshop_product')->where('eshop_eur_price_without_vat >',100)->getSQL();

print_r($pdo->query(SQLquery));

?>
--EXPECTF--
