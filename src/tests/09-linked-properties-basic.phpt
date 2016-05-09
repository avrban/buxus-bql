--TEST--
Výber linkovacej vlastnosti so zoradením podľa hodnoty sub-field (SELECT, ORDER BY)
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";

$BQLquery="SELECT page_name AS doprava, eshop_payment_type AS platba FROM eshop_transport_type ORDER BY doprava, platba.order_index";
$SQLquery=$bql->getSQL($BQLquery);

$result=$pdo->query($SQLquery)->fetchAll();

foreach($result as $row){
    if(strcmp($last,$row["doprava"])!=0) echo $row["doprava"].":\n";
    echo $row["platba.page_name"]."\n";
    $last=$row["doprava"];
}

?>
--EXPECTF--
Doručenie kuriérskou službou po úhrade objednávky zálohovou faktúrou:
online UCB UniPlatba
online Volksbank VeBpay
online VÚB
online SporoPay
Na dobierku
Doručenie na dobierku:
Na dobierku
Vyzdvihnutie priamo u predajcu:
online UCB UniPlatba
online Volksbank VeBpay
online VÚB
online SporoPay
Zaplatiť u predajcu
