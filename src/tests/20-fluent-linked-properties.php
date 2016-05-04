--TEST--
select s vyuzitim linked properties
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/*TODO: nefunguje COUNT*/
$BQLquery="SELECT page_name AS doprava, COUNT(eshop_payment_type) AS moznosti FROM eshop_transport_type GROUP BY page_name";

$SQLquery=$qb->from('eshop_transport_type')
    ->select('page_name AS doprava, COUNT(eshop_payment_type) AS moznosti')
    ->groupBy('page_name')
    ->getSQL();

print_r($pdo->query(SQLquery));

?>
--EXPECTF--
