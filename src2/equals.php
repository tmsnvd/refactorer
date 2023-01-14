<?php

// check or both files ar equal

require_once '../vendor/autoload.php';

$dataAll = explode('||linesplit||', file_get_contents('/home/tomas/dev_a3k/interlink/out2.file'));

$dataOut = [];
foreach ($dataAll as $line) {
    if (empty($line)) {
        continue;
    }
    $data = explode('||innersplit||', $line);

    [$file, $line, $create] = $data;

    $dataOut[$file][$line] = 1;
}

ksort($dataOut);

$dataAll = explode('||linesplit||', file_get_contents('/home/tomas/dev_a3k/interlink/out3.file'));

$dataOut2 = [];
foreach ($dataAll as $line) {
    if (empty($line)) {
        continue;
    }
    $data = explode('||innersplit||', $line);

    [$file, $line, $create] = $data;

    $dataOut2[$file][$line] = 1;
}

ksort($dataOut2);

// array_diff_assoc($dataOut, $dataOut2);

function array_diff_assoc_recursive($array1, $array2)
{
    $difference = [];
    foreach ($array1 as $key => $value) {
        $count1 = count($value);
        $count2 = count($array2[$key]);
        if ($count1 !== $count2) {
            $difference[$key] = $count1.', '.$count2;
        }
    }

    return $difference;
}

print_r(array_diff_assoc_recursive($dataOut, $dataOut2));

$dataOut;
