--TEST--
select s group by
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";

$SQLquery=$qb->from('eshop_product ep')
    ->innerJoin('eshop_category ec ON ec.page_id=ep.parent_page_id')
    ->select(NULL)
    ->select('COUNT(ep.page_name) AS pocet, ec.page_name AS kategoria')
    ->groupBy('ec.page_name')
    ->orderBy('pocet,ec.page_name DESC')
    ->getSQL();

$result=$pdo->query($SQLquery)->fetchAll();

foreach($result as $row){
    echo $row["kategoria"]." ".$row["pocet"]."\n";
}

?>
--EXPECTF--
Zelenina 3
Ovocie 3
Hríby 3
