--TEST--
select so zoradenim a limitom s offsetom
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";

$SQLquery=$qb->from('eshop_product')
    ->select('page_name AS produkt, eshop_eur_price_without_vat AS cena')
    ->orderBy('eshop_eur_price_without_vat DESC')
    ->limit(5)->offset(5)->getSQL();

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
