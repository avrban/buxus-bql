--TEST--
Výber konkrétnych vlastností stránok s používateľsky definovaným aliasom (SELECT, AS) s využitím Fluent rozhrania
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";

$SQLquery=$qb->from('eshop_product')->select('page_name AS produkt, eshop_eur_price_without_vat AS cena')->getSQL();

$result=$pdo->query($SQLquery)->fetchAll();
foreach($result as $row){
    echo $row["produkt"]." ".$row["cena"]."\n";
}
?>
--EXPECTF--
Muchotrávka 1.5
Šampióny 145
Hríb dubový 47
Uhorka 47
Reďkovka 49
Paprika 98
Mrkva 4.5
Kaleráb 48
Broskyne 25
Jablká 1.5
Maliny 4
Kiwi 10
Hrozno 2
Černice 7
