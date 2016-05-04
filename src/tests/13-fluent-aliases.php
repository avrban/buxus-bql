--TEST--
select na vybrane stlpce s pouzivatelsky definovanym aliasom
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";

$SQLquery=$qb->from('eshop_product')->select('page_name AS produkt, eshop_eur_price_without_vat AS cena')->getSQL();


print_r($pdo->query(SQLquery));

?>
--EXPECTF--
