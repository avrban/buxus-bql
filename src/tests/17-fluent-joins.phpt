--TEST--
Výber vlastností z viacerých typov stránok (JOIN) prostredníctvom Fluent rozhrania
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";

$SQLquery=$qb->from('eshop_product ep')
    ->innerJoin('eshop_category ec ON ep.parent_page_id=ec.page_id')
    ->select(NULL)
    ->select('ep.page_name AS produkt, ec.page_name AS kategoria')
    ->orderBy('ec.page_name')
    ->getSQL();

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
