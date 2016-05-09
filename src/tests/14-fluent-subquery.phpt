--TEST--
Výber stránok s filtrovaním podľa hodnoty vnoreného dopytu (WHERE) prostredníctvom fluent rozhrania
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";

$BQLsubquery=$qb->from('eshop_product')->select(NULL)->select('AVG(eshop_eur_price_without_vat)')->getBQL();
$SQLquery=$qb->from('eshop_product')
    ->select('page_name AS produkt, eshop_eur_price_without_vat AS cena')
    ->where('eshop_eur_price_without_vat > ('.$BQLsubquery.')')
    ->getSQL();

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
