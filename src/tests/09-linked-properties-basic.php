--TEST--
select s vyuzitim linked properties
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";

$BQLquery="SELECT page_name AS doprava,eshop_payment_type AS platba FROM eshop_transport_type";
$SQLquery=$bql->getSQL($query);

print_r($pdo->query(SQLquery));

?>
--EXPECTF--
