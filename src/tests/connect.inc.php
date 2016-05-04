<?php
/**
 * Created by PhpStorm.
 * User: avrban
 * Date: 04.05.2016
 * Time: 10:00
 */

namespace Buxus\Bql\Tests;
use Buxus\Bql\Bql;
use Buxus\Bql\QueryBuilder;
use PDO;

$pdo = new PDO("sqlite:testdb.sqlite", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);

$bql=new Bql();
$qb=new QueryBuilder();