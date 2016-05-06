--TEST--
select s group by
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";

$BQLquery="SELECT COUNT(ep.page_name) AS pocet, ec.page_name AS kategoria FROM eshop_product ep JOIN eshop_category ec ON ec.page_id = ep.parent_page_id GROUP BY ec.page_name ORDER BY pocet,ec.page_name DESC";
$SQLquery=$bql->getSQL($BQLquery);

$result=$pdo->query($SQLquery)->fetchAll();

foreach($result as $row){
    echo $row["kategoria"]." ".$row["pocet"]."\n";
}

?>
--EXPECTF--
Zelenina 3
Ovocie 3
Hríby 3
