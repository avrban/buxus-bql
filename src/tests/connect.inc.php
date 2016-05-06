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

define ('BASE_BUXUS_DIR', realpath(__DIR__ .'/../../../../../'));
require_once __DIR__ . '/../../../../../vendor/autoload.php';
require_once(CORE_BUXUS_DIR . '/src/buxus_bootstrap.php');


        $pdo = new PDO("sqlite:testdb.sqlite", "");

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);

        $bql = new Bql();
        $qb = new QueryBuilder($pdo);

