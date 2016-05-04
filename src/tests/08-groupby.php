--TEST--
select s group by
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/*TODO: nefunguje orderby*/
$BQLquery="SELECT COUNT(ep.page_name) AS pocet, ec.page_name AS kategoria FROM eshop_product ep JOIN eshop_category ec ON ec.page_id = ep.parent_page_id GROUP BY ec.page_name ORDER BY pocet";
$SQLquery=$bql->getSQL($query);

print_r($pdo->query(SQLquery));

?>
--EXPECTF--
