--TEST--
Výber všetkých stránok zadaného typu s filtrovaním podľa hodnoty vlastností (WHERE)
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";

$BQLquery="SELECT * FROM eshop_product WHERE eshop_eur_price_without_vat > 100";
$SQLquery=$bql->getSQL($BQLquery);

$result=$pdo->query($SQLquery)->fetchAll();
echo count($result)."\n";
echo $result[0]["page_name"]."\n";
?>
--EXPECTF--
1
Šampióny
