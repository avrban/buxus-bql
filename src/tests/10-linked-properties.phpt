--TEST--
Výber linkovacej vlastnosti s použitím agregačnej funkcie, zoskupením podľa vlastnosti,
filtrovaním podľa agregačnej funkcie a zoradením podľa viacerých vlastností (SELECT, GROUP BY, HAVING, ORDER BY)
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";

$BQLquery="SELECT page_name AS doprava, COUNT(eshop_payment_type) AS moznosti FROM eshop_transport_type GROUP BY page_name HAVING moznosti>4 ORDER BY moznosti, doprava";
$SQLquery=$bql->getSQL($BQLquery);

$result=$pdo->query($SQLquery)->fetchAll();

foreach($result as $row){
    echo $row["doprava"]." ".$row["moznosti"]."\n";
}
?>
--EXPECTF--
Doručenie kuriérskou službou po úhrade objednávky zálohovou faktúrou 5
Vyzdvihnutie priamo u predajcu 5
