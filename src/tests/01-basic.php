--TEST--
jednoduchy select tabulky prostrednictvom BQL
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";

$BQLquery="SELECT * FROM eshop_product";
$SQLquery=$bql->getSQL($query);

print_r($pdo->query(SQLquery));

?>
--EXPECTF--
