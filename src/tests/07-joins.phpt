--TEST--
Výber vlastností z viacerých typov stránok (JOIN)
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
$BQLquery="SELECT ep.page_name AS produkt, ec.page_name AS kategoria FROM eshop_product ep JOIN eshop_category ec ON ec.page_id = ep.parent_page_id ORDER BY ec.page_name";
$SQLquery=$bql->getSQL($BQLquery);

$result=$pdo->query($SQLquery)->fetchAll();

foreach($result as $row){
    if($last!=$row["kategoria"]) echo $row["kategoria"].":\n";
    echo $row["produkt"]."\n";
    $last=$row["kategoria"];
}

?>
--EXPECTF--
Hríby:
Muchotrávka
Šampióny
Hríb dubový
Ovocie:
Broskyne
Jablká
Maliny
Zelenina:
Uhorka
Reďkovka
Paprika
