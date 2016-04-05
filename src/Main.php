<?php
namespace Buxus\Bql;

//require '../../../autoload.php';

use Buxus\Bql;
use PHPSQLParser\PHPSQLParser;


class Main
{
    function execute($query){

        $parser=new PHPSQLParser();
        $parsed=$parser->parse($query);
        print_r($parsed);

        $walker=new SqlWalker($parsed,0);
        $sql=$walker->getSQL();

        echo $sql."\n";
    }
}
/*
global $argc, $argv;

$parser=new PHPSQLParser();
$parsed=$parser->parse($argv[1]);
print_r($parsed);

$walker=new SqlWalker($parsed);
$sql=$walker->getSQL();

echo $sql;*/
?>