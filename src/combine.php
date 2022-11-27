<?php

require_once '../vendor/autoload.php';

use PhpMyAdmin\SqlParser\Components\CreateDefinition;
use PhpMyAdmin\SqlParser\Components\DataType;
use PhpMyAdmin\SqlParser\Parser;

$my_conn = new PDO('sqlite:../q.db');
$my_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "select * from sql_table where create_columns <> 'wrong db' and combined = 0";

foreach ($my_conn->query($sql) as $row) {
    $createQuery = trim($row['create_query']);
    if (!str_contains('(', $createQuery)) {
        $createQuery .= '()';
    }

    $sqlParser = new Parser($createQuery);

    $columns = explode(',', $row['create_columns']);
    foreach ($columns as $column) {
        $column = explode(' ', trim($column));
        $dataType = new DataType($column[1]);
        $createDefinition = new CreateDefinition($column[0], null, $dataType);
        $sqlParser->statements[0]->fields[] = $createDefinition;
    }

    $query = "UPDATE sql_table SET create_query=:create_query, combined = 1 WHERE id=:id";
    $sql = $my_conn->prepare($query);

    $sql->bindValue(':create_query', $sqlParser->statements[0]->build());
    $sql->bindParam(':id', $row['id'], PDO::PARAM_INT);

    if ($sql->execute()) {
        echo "Successfully updated record ".PHP_EOL;
    } else {
        print_r($sql->errorInfo());
    }
}

