--TEST--
Výber s použitím agregačných funkcií (AVG(), SUM(), MIN(), MAX(), COUNT())
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";

$BQLquery="SELECT AVG(eshop_eur_price_without_vat) AS priemer, SUM(eshop_eur_price_without_vat) AS suma, MIN(eshop_eur_price_without_vat) AS minimum, MAX(eshop_eur_price_without_vat) AS maximum, COUNT(page_name) AS pocet FROM eshop_product";
$SQLquery=$bql->getSQL($BQLquery);

$result=$pdo->query($SQLquery)->fetchAll();
echo round($result[0]["priemer"],2)."\n";
echo $result[0]["suma"]."\n";
echo $result[0]["minimum"]."\n";
echo $result[0]["maximum"]."\n";
echo $result[0]["pocet"]."\n";
?>
--EXPECTF--
34.96
489.5
1.5
145
14
