<?php


$my_conn = new PDO('sqlite:../q.db');
$my_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$db_host = "kb-qa-ilinkdb501.a3k.local";
$db_user = "d0ck3r";
$db_pswd = "e2k2MbC6";

$mysqli = new mysqli($db_host, $db_user, $db_pswd, "interlink");

if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

function interference(string $query, $mysqli)
{
    if (trim($query) === '') {
        return;
    }

    $mysql_data_type_hash = [
        1 => 'tinyint',
        2 => 'smallint',
        3 => 'int',
        4 => 'float',
        5 => 'double',
        7 => 'timestamp',
        8 => 'bigint',
        9 => 'mediumint',
        10 => 'date',
        11 => 'time',
        12 => 'datetime',
        13 => 'year',
        16 => 'bit',
        252 => 'text',
        //252 is currently mapped to all text and blob types (MySQL 5.0.51a)
        253 => 'varchar',
        254 => 'char',
        246 => 'decimal'
    ];

    $remap_types = [
        'varchar' => '255', //unless is bigger length
    ];

    $query .= " LIMIT 1";
    $statement = '';

    $SUBSCRIBER_TYPES["kidbiz"] = 6;
    $SUBSCRIBER_TYPES["district"] = 5;
    $SUBSCRIBER_TYPES["school"] = 4;
    $SUBSCRIBER_TYPES["parent"] = 7;
    $SUBSCRIBER_TYPES["teacher"] = 2;
    $SUBSCRIBER_TYPES["student"] = 1;

    $ASSESSMENT["pre"] = 1;
    $ASSESSMENT["mid"] = 2;
    $ASSESSMENT["mid2"] = 3;
    $ASSESSMENT["post"] = 4;
    $ASSESSMENT["summer_pre"] = 5;
    $ASSESSMENT["summer_post"] = 6;
    $ASSESSMENT["vocab_pre"] = 7;
    $ASSESSMENT["vocab_post"] = 8;
    $ASSESSMENT["pre_2"] = 9;
    $ASSESSMENT["placement"] = 10;
    $ASSESSMENT["pre_writing"] = 11;
    $ASSESSMENT["interim_writing"] = 12;
    $ASSESSMENT["post_writing"] = 13;
    $ASSESSMENT["summer_extension_pre"] = 14;

    $ASSESSMENT_DATE["pre_2"] = 33;
    $SUMMER_ASSESSMENTS = array(
        $ASSESSMENT["summer_pre"],
        $ASSESSMENT["summer_post"],
        $ASSESSMENT["vocab_pre"],
        $ASSESSMENT["vocab_post"],
        $ASSESSMENT["summer_extension_pre"]
    );

    try {
        $query = str_replace(
            [
                '".date("Y-m-d", $start_date)."',
                '".date("Y-m-d", $end_date)."',
                '" . date("Y-m-d", $end_date) . "',
                '".date("Y-m-d", $yesterday)."',
                '" . $SUBSCRIBER_TYPES["student"] . "',
                '" . $SUBSCRIBER_TYPES["teacher"] . "',
                '\' . implode(\', \', $school_years) . \'',
            ],
            [
                '2020-01-01',
                '2020-01-02',
                '2020-01-02',
                '2020-01-02',
                $SUBSCRIBER_TYPES["student"],
                $SUBSCRIBER_TYPES["teacher"],
                '2020',
            ],
            $query
        );
        print_r($query);
        if ($result = $mysqli->query($query)) {
            $field = [];

            /* Get field information for all columns */
            while ($finfo = $result->fetch_field()) {
//            printf("Name:     %s\n", $finfo->name);
//            printf("Table:    %s\n", $finfo->table);
//            printf("max. Len: %d\n", $finfo->max_length);
//            printf("Flags:    %d\n", $finfo->flags);
//            printf("Type:     %d\n\n", $mysql_data_type_hash[$finfo->type] ?? '???');

                $field[$finfo->name] = $finfo->name . ' ' . $mysql_data_type_hash[$finfo->type] . '(' . $finfo->length . ')';
            }
            $result->close();

            $statement .= implode(',' . PHP_EOL, $field);
            // $statement;
        }
    } catch (Exception $e) {
        $statement = 'wrong db';
    }

    return $statement;
}


$sql = "SELECT * FROM sql_table WHERE skip = 0";
foreach ($my_conn->query($sql) as $row) {
    if (trim($row['select_query']) === '') {
        continue;
    }

    $statement = interference($row['select_query'], $mysqli);
    if ($statement === 'wrong db') {
        continue;
    }

    $query = "UPDATE sql_table SET create_columns=:create_columns,skip=1 WHERE id=:id";
    $sql = $my_conn->prepare($query);

    $sql->bindValue(':create_columns', $statement);
    $sql->bindParam(':id', $row['id'], PDO::PARAM_INT);

    print_r($statement . PHP_EOL . PHP_EOL);

    if ($sql->execute()) {
        echo "Successfully updated record " . PHP_EOL;
    } else {
        print_r($sql->errorInfo()); // if any error is there it will be posted
        $msg = " Database problem, please contact site admin ";
    }
}

$mysqli->close();
