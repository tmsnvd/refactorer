<?php

$my_conn = new PDO('sqlite:../q.db');
$my_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "select * from sql_table where select_query =''";

foreach ($my_conn->query($sql) as $row) {
    $createQuery = trim($row['original_query']);

    $query = "UPDATE sql_table SET select_query=:select_query WHERE id=:id";
    $sql = $my_conn->prepare($query);

    $createQuery = explode('SELECT', $createQuery);

    $sql->bindValue(':select_query', $createQuery[0]);
    $sql->bindParam(':id', $row['id'], PDO::PARAM_INT);

    if ($sql->execute()) {
        echo "Successfully updated record ".PHP_EOL;
    } else {
        print_r($sql->errorInfo());
    }
}

