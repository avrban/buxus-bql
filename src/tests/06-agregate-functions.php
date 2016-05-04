--TEST--
select so zoradenim a limitom s offsetom
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";

/*TODO nefunguju aliasy*/
$BQLquery="SELECT AVG(eshop_eur_price_without_vat) AS priemer, SUM(eshop_eur_price_without_vat) AS suma, MIN(eshop_eur_price_without_vat) AS minimum, MAX(eshop_eur_price_without_vat) AS maximum, COUNT(page_name) AS pocet FROM eshop_product";
$SQLquery=$bql->getSQL($query);

print_r($pdo->query(SQLquery));

?>
--EXPECTF--
