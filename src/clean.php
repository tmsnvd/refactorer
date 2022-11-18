<?php

$my_conn = new PDO('sqlite:../q.db');
$my_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "SELECT * FROM sql_table";

$re = '/(as[\S\s*])*select[\S\s*]*/mi';
foreach ($my_conn->query($sql) as $row) {
    $result = preg_replace($re, '', $row['original_query']);

    $query = "UPDATE sql_table SET create_query=:create_query WHERE id=:id";
    $sql = $my_conn->prepare($query);

    $sql->bindValue(':create_query', trim($result));
    $sql->bindParam(':id', $row['id'], PDO::PARAM_INT);

    if ($sql->execute()) {
        echo "Successfully updated record ";
    } else {
        print_r($sql->errorInfo()); // if any error is there it will be posted
        $msg = " Database problem, please contact site admin ";
    }
}

if ($sql->execute()) {
    echo "Successfully updated record ";
    echo "<br><br>Number of rows updated : ".$sql->rowCount();
}
