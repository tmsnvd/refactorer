<?php

use PhpMyAdmin\SqlParser\Utils\Formatter;

require_once '../vendor/autoload.php';

$my_conn = new PDO('sqlite:../new.db');
$my_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "select id, `create`, create_columns, old from sql_table";
$results = $my_conn->query($sql);

foreach ($results as $row) {
    $compare = "
SELECT ||columns||
FROM (
SELECT ||columns|| FROM ||t1||
UNION ALL
SELECT ||columns|| FROM ||t2||
) tbl
GROUP BY ||columns||
HAVING count(*) = 1;
";

    $compare = str_replace('||columns||', $row['create_columns'], $compare);

    preg_match('/CREATE TABLE ([A-z$_0-9.{}]*)/i', $row['create'], $match);
    if (!isset($match[1])) {
        echo 123;
    }
    $t1 = trim($match[1]).'_v1';
    $t2 = trim($match[1]).'_v2';
    $compare = str_replace(['||t1||', '||t2||'], [$t1, $t2], $compare);

    $insert = "UPDATE sql_table SET `compare` = :compare, table1 = :t1, table2 = :t2 WHERE id = ".$row['id'];
    $sql = $my_conn->prepare($insert);
    $compare = Formatter::format(trim($compare) ?? '', ['type' => 'txt']);
    $sql->bindValue(':compare', $compare);
    $sql->bindValue(':t1', $t1);
    $sql->bindValue(':t2', $t2);

    if ($sql->execute()) {
        echo "Successfully updated record ".PHP_EOL;
    } else {
        print_r($sql->errorInfo());
    }
}
