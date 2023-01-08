<?php

require_once '../vendor/autoload.php';

use PhpMyAdmin\SqlParser\Components\CreateDefinition;
use PhpMyAdmin\SqlParser\Components\DataType;
use PhpMyAdmin\SqlParser\Parser;

$my_conn = new PDO('sqlite:../q.db');
$my_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "select * from sql_table where uses_temp_table = 0 AND very_slow_query = 0 AND combined = 1";

foreach ($my_conn->query($sql) as $row) {
    $createQuery = trim($row['create_query']);
    $sqlParser = new Parser($createQuery);

    if (isset($sqlParser->statements[0]->name->table)) {
        $insert = 'INSERT INTO ' . $sqlParser->statements[0]->name->table . ' ' . PHP_EOL;
        $query = "UPDATE sql_table SET select_query=:select_query, dbtable=:dbtable WHERE id=:id";
        $sql = $my_conn->prepare($query);

        $sql->bindValue(':select_query', $insert . $row['select_query']);
        $sql->bindValue(':dbtable', $sqlParser->statements[0]->name->table);
        $sql->bindParam(':id', $row['id'], PDO::PARAM_INT);

        if ($sql->execute()) {
            echo "Successfully updated record " . PHP_EOL;
        } else {
            print_r($sql->errorInfo());
        }
    }
}

