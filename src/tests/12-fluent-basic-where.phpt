--TEST--
jednoduchy select tabulky s filtrovanim
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";

$SQLquery=$qb->from('eshop_product')->where('eshop_eur_price_without_vat >',100)->getSQL();

$result=$pdo->query($SQLquery)->fetchAll();
echo count($result)."\n";
echo $result[0]["page_name"]."\n";
?>
--EXPECTF--
1
Šampióny
