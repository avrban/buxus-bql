<?php
/**
 * Created by PhpStorm.
 * User: avrban
 * Date: 06.05.2016
 * Time: 19:39
 */
$opts = getopt('vdh');
$debug = array_key_exists('v', $opts);
$detail = array_key_exists('d',$opts);

$error = false;

$start=microtime(true);
$tests = glob(dirname(__FILE__) . "/*.phpt", GLOB_NOSORT);
natsort($tests);

$i=0;
$uspesne=0;
$neuspesne=0;
foreach ($tests as $filename) {
    $i++;
    $teststart=microtime(true);
    echo "Test č. ".$i.": ".substr($filename, strrpos($filename, '/') + 1)."\n";

    ob_start();
    include $filename;

    if (!preg_match("~^--TEST--\n(.*?)\n(?:--SKIPIF--\n(.*\n)?)?--FILE--\n(.*\n)?--EXPECTF--\n(.*)~s", str_replace("\r\n", "\n", ob_get_clean()), $casti))    {
        echo "Chyba: nesprávna syntax testovacieho súbrou!\n---\n";
        $neuspesne++;
        continue;
    }

    if ($casti[3] !== $casti[4]) {
        echo "Chyba: test nemá očakávaný výstup!\n";
        if ($debug || $detail) {
            echo "\n--očakávaný výstup--\n", $casti[4], "\n--skutočný výstup--\n", $casti[3], "\n---\n";
            $neuspesne++;
            continue;
        }
    }
    else {
        if ($detail) {
            echo "\n--očakávaný výstup--\n", $casti[4], "\n--skutočný výstup--\n", $casti[3], "\n";
        }

        printf("Test úspešný za %.3F s\n", microtime(time) - $teststart);
        echo "---\n";
        $uspesne++;
    }
}
printf("Testovanie ukončené za %.3F s, spotrebovaná pamäť: %d KiB\nÚspešných: %d/%d | Neúspešných: %d/%d\n", microtime(true) - $start, memory_get_peak_usage() / 1024,$uspesne,$i,$neuspesne,$i);
if ($error) exit(1);