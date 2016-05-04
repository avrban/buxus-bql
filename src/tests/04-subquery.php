--TEST--
select s filtrovanim podla vnoreneho dopytu
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";

$BQLquery="SELECT page_name AS produkt, eshop_eur_price_without_vat AS cena FROM eshop_product WHERE eshop_eur_price_without_vat > (SELECT AVG(eshop_eur_price_without_vat) FROM eshop_product)";
$SQLquery=$bql->getSQL($query);

print_r($pdo->query(SQLquery));

?>
--EXPECTF--


