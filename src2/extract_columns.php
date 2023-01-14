<?php

require_once '../vendor/autoload.php';

$my_conn = new PDO('sqlite:../new.db');
$my_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "select id, `create` from sql_table";
$results = $my_conn->query($sql);
foreach ($results as $row) {
    $columns = explode('`', $row['create']);
    // oops
    $collection = [];
    foreach ($columns as $key => $name) {
        if ($key % 2 !== 0) {
            $collection[] = $name;
        }
    }

    $insert = "UPDATE sql_table SET `create_columns` = :old WHERE id = ".$row['id'];
    $sql = $my_conn->prepare($insert);
    $sql->bindValue(':old', implode(', ', $collection));

    if ($sql->execute()) {
        echo "Successfully updated record ".PHP_EOL;
    } else {
        print_r($sql->errorInfo());
    }
}
