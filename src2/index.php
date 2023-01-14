<?php

require_once '../vendor/autoload.php';

use PhpMyAdmin\SqlParser\Exceptions\ParserException;
use PhpMyAdmin\SqlParser\Parser;

use erguncaner\Table\Table;
use erguncaner\Table\TableColumn;
use erguncaner\Table\TableRow;
use erguncaner\Table\TableCell;

// phpgrep -format '||linesplit||{{.Filename}}||innersplit||{{.Line}}||innersplit||{{.a}}' ./ '$f = $a' 'f~createTable' 2>/dev/null 1> out2.file

$dataAll = explode('||linesplit||', file_get_contents('/home/tomas/dev_a3k/interlink/out2.file'));

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

foreach ($dataOut as $file => $lineCreate) {
    foreach ($lineCreate as $line => $create) {
        $insert = 'INSERT INTO sql_table (`file`, `line`, `create`) VALUES(:file, :line, :create)';
        $sql = $my_conn->prepare($insert);

        $sql->bindValue(':file', $file);
        $sql->bindValue(':line', $line, PDO::PARAM_INT);
        $sql->bindParam(':create', $create);

        if ($sql->execute()) {
            echo "Successfully updated record ".PHP_EOL;
        } else {
            print_r($sql->errorInfo());
        }
    }
}
