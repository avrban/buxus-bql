--TEST--
select s group by
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/*TODO: nefunguje orderby*/

$SQLquery=$qb->from('eshop_product')
    ->select('COUNT(eshop_product.page_name) AS pocet, eshop_category.page_name AS kategoria')
    ->groupBy('eshop_category.page_name')
    ->orderBy('pocet')
    ->getSQL();

print_r($pdo->query(SQLquery));

?>
--EXPECTF--
