<?php

require_once '../vendor/autoload.php';

use PhpMyAdmin\SqlParser\Exceptions\ParserException;
use PhpMyAdmin\SqlParser\Parser;

use erguncaner\Table\Table;
use erguncaner\Table\TableColumn;
use erguncaner\Table\TableRow;
use erguncaner\Table\TableCell;

// phpgrep -format '||linesplit||{{.Filename}}||innersplit||{{.Line}}||innersplit||{{.f}}' ./ '$_ = $f' 'f~(?i)\/\*\*[0-9]{1,5}\*\*\/' 2>/dev/null 1> out3.file

$dataAll = explode('||linesplit||', file_get_contents('/home/tomas/dev_a3k/interlink/out3.file'));

$my_conn = new PDO('sqlite:../new.db');
$my_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$dataOut = [];
foreach ($dataAll as $line) {
    if (empty($line)) {
        continue;
    }
    $data = explode('||innersplit||', $line);

    [$file, $line, $create] = $data;

    $dataOut[$file][$line] = $create;
}

ksort($dataOut);

$sql = "select * from sql_table order by file";
$results = $my_conn->query($sql);
foreach ($results as $row) {
    $file = key($dataOut);
    $lineCreate = array_shift($dataOut);
    if ($row['file'] !== $file) {
        die('wrong!');
    }
    $i = 0;
    foreach ($lineCreate as $line => $create) {
        $i++;
        if ($i > 1) {
            $row = $results->fetch();
        }
        $insert = "UPDATE sql_table SET `insert` = :insert WHERE id = ".$row['id'];
        $sql = $my_conn->prepare($insert);
        $sql->bindParam(':insert', $create);

        if ($sql->execute()) {
            echo "Successfully updated record ".PHP_EOL;
        } else {
            print_r($sql->errorInfo());
        }
    }
}
