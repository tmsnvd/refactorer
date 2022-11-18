<?php


$my_conn = new PDO('sqlite:../q.db');
$my_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


$mysqli = new mysqli("localhost", "my_user", "my_password", "world");

if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

function interference(string $query, $mysqli)
{
    if (trim($query) === '') {
        return;
    }

    // $query = "SELECT Name, SurfaceArea from Country ORDER BY Code LIMIT 5";

    if ($result = $mysqli->query($query)) {
        /* Get field information for all columns */
        while ($finfo = $result->fetch_field()) {
            printf("Name:     %s\n", $finfo->name);
            printf("Table:    %s\n", $finfo->table);
            printf("max. Len: %d\n", $finfo->max_length);
            printf("Flags:    %d\n", $finfo->flags);
            printf("Type:     %d\n\n", $finfo->type);
        }
        $result->close();
    }
}


$sql = "SELECT * FROM sql_table";
foreach ($my_conn->query($sql) as $row) {
    interference($row['select_query'], $mysqli);
}

$mysqli->close();
