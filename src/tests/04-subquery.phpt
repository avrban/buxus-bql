--TEST--
Výber stránok s filtrovaním podľa hodnoty vnoreného dopytu (WHERE)
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";

$BQLquery="SELECT page_name AS produkt, eshop_eur_price_without_vat AS cena FROM eshop_product WHERE eshop_eur_price_without_vat > (SELECT AVG(eshop_eur_price_without_vat) FROM eshop_product) ORDER BY page_id";
$SQLquery=$bql->getSQL($BQLquery);

$result=$pdo->query($SQLquery)->fetchAll();
foreach($result as $row){
    echo $row["produkt"]." ".$row["cena"]."\n";
}
?>
--EXPECTF--
Šampióny 145
Hríb dubový 47
Uhorka 47
Reďkovka 49
Paprika 98
Kaleráb 48
