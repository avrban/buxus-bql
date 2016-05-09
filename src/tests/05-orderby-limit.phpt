--TEST--
Výber stránok so zoraďovaním podľa hodnôt ich vlastností, s preskočením určeného počtu stránok
a obmedzením celkového počtu výsledkov (ORDER BY, LIMIT)
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";

$BQLquery="SELECT page_name AS produkt, eshop_eur_price_without_vat AS cena FROM eshop_product ORDER BY eshop_eur_price_without_vat DESC LIMIT 5, 5";
$SQLquery=$bql->getSQL($BQLquery);

$result=$pdo->query($SQLquery)->fetchAll();
foreach($result as $row){
    echo $row["produkt"]." ".$row["cena"]."\n";
}
?>
--EXPECTF--
Uhorka 47
Broskyne 25
Kiwi 10
Černice 7
Mrkva 4.5
