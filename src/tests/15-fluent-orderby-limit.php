--TEST--
select so zoradenim a limitom s offsetom
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";

$SQLquery=$qb->from('eshop_product')
    ->select('page_name AS produkt, eshop_eur_price_without_vat AS cena')
    ->orderBy('eshop_eur_price_without_vat')
    ->limit(10)->offset(5)->getSQL();

print_r($pdo->query(SQLquery));

?>
--EXPECTF--
