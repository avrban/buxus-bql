--TEST--
jednoduchy select tabulky prostrednictvom fluent api
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";

$SQLquery=$qb->from('eshop_product')->getSQL();

$result=$pdo->query($SQLquery)->fetchAll();
echo count($result)."\n";
?>
--EXPECTF--
14
