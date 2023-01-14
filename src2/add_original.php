<?php

//git checkout to develop !!!

require_once '../vendor/autoload.php';

use PhpMyAdmin\SqlParser\Exceptions\ParserException;
use PhpMyAdmin\SqlParser\Parser;

use erguncaner\Table\Table;
use erguncaner\Table\TableColumn;
use erguncaner\Table\TableRow;
use erguncaner\Table\TableCell;

// out.file

$dataAll = explode('||linesplit||', file_get_contents('/home/tomas/dev_a3k/interlink/out.file'));

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
    $fileArray = $dataOut[$row['file']];
    if (isset($fileArray[$row['old_line']])) { // THE right record !!!
        $insert = "UPDATE sql_table SET `old` = :old WHERE id = ".$row['id'];
        $sql = $my_conn->prepare($insert);
        $sql->bindParam(':old', $fileArray[$row['old_line']]);

        if ($sql->execute()) {
            echo "Successfully updated record ".PHP_EOL;
        } else {
            print_r($sql->errorInfo());
        }
    }
}
