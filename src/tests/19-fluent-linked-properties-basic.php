--TEST--
select s vyuzitim linked properties
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";

$SQLquery=$qb->from('eshop_transport_type')
    ->select('page_name AS doprava, eshop_payment_type AS platba')
    ->getSQL();

print_r($pdo->query(SQLquery));

?>
--EXPECTF--
