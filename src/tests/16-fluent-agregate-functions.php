--TEST--
select so zoradenim a limitom s offsetom
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";

$SQLquery=$qb->from('eshop_product')
    ->select('AVG(eshop_eur_price_without_vat) AS priemer, SUM(eshop_eur_price_without_vat) AS suma, MIN(eshop_eur_price_without_vat) AS minimum, MAX(eshop_eur_price_without_vat) AS maximum, COUNT(page_name) AS pocet')
    ->getSQL();

print_r($pdo->query(SQLquery));

?>
--EXPECTF--
