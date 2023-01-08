<?php

$my_conn = new PDO('sqlite:../q.db');
$my_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "SELECT id, original_query, select_query FROM sql_table WHERE select_query LIKE '%CREATE%';";

foreach ($my_conn->query($sql) as $row) {
    $row['original_query'] = trim($row['original_query']);

    $query = "UPDATE sql_table SET select_query=:select_query WHERE id=:id";
    $sql = $my_conn->prepare($query);

    $createQuery = explode('select', $row['original_query'], 2);
    if (count($createQuery) === 2) {
        $sql->bindValue(':select_query', 'select ' . $createQuery[1]);
    } else {
        $createQuery = explode('SELECT', $row['original_query'], 2);
        $sql->bindValue(':select_query', 'SELECT ' . $createQuery[1]);
    }

    if (count($createQuery) === 2) {

        $sql->bindParam(':id', $row['id'], PDO::PARAM_INT);

        if ($sql->execute()) {
            echo "Successfully updated record " . PHP_EOL;
        } else {
            print_r($sql->errorInfo());
        }
    }
}

