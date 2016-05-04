--TEST--
select na vybrane stlpce s pouzivatelsky definovanym aliasom
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";

$BQLquery="SELECT page_name AS produkt, eshop_eur_price_without_vat AS cena FROM eshop_product";
$SQLquery=$bql->getSQL($query);

print_r($pdo->query(SQLquery));

?>
--EXPECTF--
