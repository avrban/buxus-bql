--TEST--
select so zoradenim a limitom s offsetom
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";

$BQLquery="SELECT page_name AS produkt, eshop_eur_price_without_vat AS cena FROM eshop_product ORDER BY eshop_eur_price_without_vat LIMIT 5, 10";
$SQLquery=$bql->getSQL($query);

print_r($pdo->query(SQLquery));

?>
--EXPECTF--
