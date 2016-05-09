--TEST--
Výber linkovacej vlastnosti s použitím agregačnej funkcie, zoskupením podľa vlastnosti,
filtrovaním podľa agregačnej funkcie a zoradením podľa viacerých vlastností (SELECT, GROUP BY, HAVING, ORDER BY)
prostredníctvom Fluent rozhrania
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";

$SQLquery=$qb->from('eshop_transport_type')
    ->select(NULL)
    ->select('page_name AS doprava, COUNT(eshop_payment_type) AS moznosti')
    ->groupBy('page_name')
    ->having('moznosti>4')
    ->orderBy('moznosti,doprava')
    ->getSQL();

$result=$pdo->query($SQLquery)->fetchAll();

foreach($result as $row){
    echo $row["doprava"]." ".$row["moznosti"]."\n";
}
?>
--EXPECTF--
Doručenie kuriérskou službou po úhrade objednávky zálohovou faktúrou 5
Vyzdvihnutie priamo u predajcu 5
