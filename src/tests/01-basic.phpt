--TEST--
Výber všetkých stránok zadaného typu (SELECT, FROM)
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";

$BQLquery="SELECT * FROM eshop_product";
$SQLquery=$bql->getSQL($BQLquery);

$result=$pdo->query($SQLquery)->fetchAll();
echo count($result)."\n";
?>
--EXPECTF--
14
