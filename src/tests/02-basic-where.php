--TEST--
jednoduchy select tabulky s filtrovanim
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";

$BQLquery="SELECT * FROM eshop_products WHERE eshop_eur_price_without_vat > 100";
$SQLquery=$bql->getSQL($query);

print_r($pdo->query(SQLquery));

?>
--EXPECTF--
