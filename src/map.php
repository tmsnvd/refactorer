<?php


$my_conn = new PDO('sqlite:../q.db');
$my_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$db_host = "kb-qa-ilinkdb501.a3k.local";
$db_user = "d0ck3r";
$db_pswd = "e2k2MbC6";

$defaultDb = 'interlink';

$mysqli = new mysqli($db_host, $db_user, $db_pswd, $defaultDb);

if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

function interference(string $query, $mysqli, $currentDb)
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

    $query .= " LIMIT 1";
    $statement[0] = '';
    $statement[1] = '';

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

    $program_rollup_table = "school_usage_by_program";

    $PRODUCT_GROUPS["program pricing"] = 26;
    $PRODUCT_GROUPS["pd"] = 27;
    $PRODUCT_GROUPS["discount"] = 28;
    $PRODUCT_GROUPS["levelset"] = 29;
    $PRODUCT_GROUPS["fee"] = 32;
    $PRODUCT_GROUPS["unit_building"] = 35;
    $PRODUCT_GROUPS["project_management"] = 31;
    $PRODUCT_GROUPS["individual"] = 30;
    $PRODUCT_GROUPS["data-services"] = 42;
    $PRODUCT_GROUPS["NYC-DOE-AIS"] = 43;
    $PRODUCT_GROUPS["custom development service"] = 49;
    $PRODUCT_GROUPS["textbook alignment"] = 61;
    $PRODUCT_GROUPS["eScience <500"] = 46;
    $PRODUCT_GROUPS["eScience >500"] = 47;
    $PRODUCT_GROUPS["smarty ants"] = 60;
    $PRODUCT_GROUPS["bundles"] = 62;


    try {
        $query = str_replace(
            [
                '".date("Y-m-d", $start_date)."',
                '".date("Y-m-d", $end_date)."',
                '" . date("Y-m-d", $end_date) . "',
                '".date("Y-m-d", $yesterday)."',
                '" . $SUBSCRIBER_TYPES["student"] . "',
                '" . $SUBSCRIBER_TYPES["teacher"] . "',
                '" . $SUBSCRIBER_TYPES["school"] . "',
                '\' . implode(\', \', $school_years) . \'',
                '$payment_table',
                '$db_pick',
                '$data_db',
                '$start_date_str',
                '$end_date_str',
                '$SERVER_TIMEZONE',
                '$classIds',
                '$tmp_users',
                '$tmp_users_all',
                '".($accounts ? ("AND ea.account_id IN($accounts)") : "")."',
                '".($accounts ? ("AND od.account_id IN($accounts)") : "")."',
                '".($accounts ? ("AND schools.account_id IN($accounts)") : "")."', // 1.
                '$edition_str',
                '$summer',
                '$year',
                '$report_month_filter',
                '$user_where',
                '$edition_str',
                '$num_weeks',
                '" . ($hisd_run ? "program_id = \'15\'" : "program_id IN (" . implode(", ", $use_programs) . ")") . "',
                "' . implode(', ', \$use_programs) . '",
                '$program_rollup_table',
                '" . date(\'Y-m-d\', $end_date) . "',
                '" . date(\'Y-m-d\', $start_date) . "',
                '" . $report_end_date ."',
                '$old_amortization_db',
                '".date(\'Y-m-d\',$start_date)."',
                '".date(\'Y-m-d\',$end_date)."',
                '" . str_replace("IF (se", "IF (se2", $edition_str) . "',
                '" . $PRODUCT_GROUPS["pd"] . "',
                '" . $PRODUCT_GROUPS["project_management"] . "',
                '" . $PRODUCT_GROUPS["levelset"] . "',
                '" . $PRODUCT_GROUPS["discount"] . "',
                '" . $PRODUCT_GROUPS["fee"] . "',
                '" . implode("\',\' ", $upgrade_product_groups) . "',
                '{$lock_year}',
                '$licenseStr',
                '{$lock_month_string}',
                '{$report_year}',
                '$dateRange',
                '$filter_string',
                '$addtl_tables',
                '$ay_filter',
                '" . $ROLE[$DEPARTMENT["implementation"]]["trainer"] . "',
                '" . $ROLE[$DEPARTMENT["implementation"]]["area_manager"] . "',
                '" . $ROLE[$DEPARTMENT["implementation"]]["rvp"] . "',
                '$today',
                '".$USERS["system_generated"]."',
                '" . $PRODUCT_GROUPS["program pricing"] . "',
                '" . ($sync_type == "sa" ? "AND h.column_name IN(\'end_date\', \'post_end\', \'account_id\')" : "") . "',
                '" . implode(",", $sub_products) . "',
                '$order_last_start',
                '$start_time',
                'last_start_time',
                '".SMARTY_ANTS_PROGRAM',
                '$deferred_where',
                '".date(\'Y-m-d\',$next_month)."',
                '$report_end_date',
                '" . ($refresh_orders ? " AND o.order_id IN (" . implode(", ", $refresh_orders) . ")" : "") . "',
                '".$SUB_PRODUCT_TYPES["initial training"]."',
                '".$SUB_PRODUCT_TYPES["trainer training"]."',
                '".$SUB_PRODUCT_TYPES["followup training"]."',
                '".$SUB_PRODUCT_TYPES["initial online training"]."',
                '".$SUB_PRODUCT_TYPES["online training"]."',
                '".$EVENTS["training"]."',
                '" . ($refresh_orders ? " AND e.order_id IN (" . implode(", ", $refresh_orders) . ")" : "") . "',
                '" . ($refresh_orders ? " AND order_id IN (" . implode(",",$refresh_orders) .")" : "") . "',
                '" . ($refresh_orders ? " orders o, order_details d" : "summary_orders_all o, summary_order_details_all d") . "',
                '" . ($refresh_orders ? " AND o.status = 0 AND d.status = 0 AND d.academic_year_id > 0" : "")',
                '" . ($refresh_orders ? "AND cd.order_id IN (" . implode(", ", $refresh_orders) . ")" : "") . "',
                '$whereStr',
                '$expire_start_date',
                '$order_detail_amortization',
                '$defer_table_name',
                '$table_name',
                '$id',
                '$HSBC_ID',
                '" . str_replace("IF (se", "IF (se2", $edition_str) . "',
                '" . str_replace("IF (se", "IF (se2", 0) . "',
            ],
            [
                '2022-11-01',
                '2022-11-02',
                '2022-11-02',
                '2022-11-02',
                $SUBSCRIBER_TYPES["student"],
                $SUBSCRIBER_TYPES["teacher"],
                $SUBSCRIBER_TYPES["school"],
                '2020',
                "billing.order_invc_transactions",
                $currentDb,
                $currentDb,
                '2022-11-01',
                '2022-11-02',
                '+00:00',
                '1,2',
                'tech.wu_school_users',
                'tech.wu_school_users_all',
                '',
                '',
                '', // 1.
                '0',
                '0',
                2022,
                'string',
                ' 1 = 1 ',
                ' 1 = 1 ',
                1,
                ' 1 = 1 ',
                1,
                $program_rollup_table,
                '2022-11-02',
                '2022-11-01',
                '2022-11-01',
                'amortization_old',
                '2022-11-02',
                '2022-11-03',
                ' 1 = 1 ',
                $PRODUCT_GROUPS["pd"],
                $PRODUCT_GROUPS["project_management"],
                $PRODUCT_GROUPS["levelset"],
                $PRODUCT_GROUPS["discount"],
                $PRODUCT_GROUPS["fee"],
                1,
                '2011',
                '',
                '20111201',
                '2021',
                '', // $dateRange
                '', // filter_string
                ", iplan_values ivals ",
                1, //$ay_filter
                1,
                1,
                1,
                '2022-11-30', //$today
                1,
                $PRODUCT_GROUPS["program pricing"],
                ' ',
                '1',
                '2022-11-03',
                '2022-11-03',
                '2022-11-03',
                '1',
                '', // $deferred_where
                '2022-11-03',
                '2022-11-03',
                '', // " . ($refresh_orders ? " AND o.order_id IN (" . implode(", ", $refresh_orders) . ")" : "") . "
                1,
                1,
                1,
                1,
                1, // ".$SUB_PRODUCT_TYPES["online training"]."
                1,
                '',
                '',
                'summary_orders_all o, summary_order_details_all d',
                '', // " . ($refresh_orders ? " AND o.status = 0 AND d.status = 0 AND d.academic_year_id > 0" : "")
                '',
                '', // $whereStr
                '2022-11-10', // $expire_start_date
                'order_detail_amortization_lock',
                'amortization_deferred_report_20011231',
                'monthly_new_renew_report_20221031',
                'sa_id', // $id
                '177836',
                ' 1 = 1 ',
                ' 1 = 1 ',
            ],
            $query
        );
        // print_r($query);

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

            $statement[1] = implode(',' . PHP_EOL, $field);
            // $statement;
        }
    } catch (Exception $e) {
        // print_r($e);
        if (str_contains($e->getMessage(), "doesn't")) {
            $statement[0] = 'wrong db';
        } else {
            $statement[0] = 'other error';
        }

        $statement[1] = 'wrong ' . $e->getMessage();
    }

    return $statement;
}


// $sql = "SELECT * FROM sql_table WHERE create_columns LIKE '%n''t exist%' AND uses_temp_table = 0 AND very_slow_query = 0";
$sql = "SELECT * FROM sql_table WHERE create_columns LIKE '%IF (%' AND uses_temp_table = 0 AND very_slow_query = 0";

foreach ($my_conn->query($sql) as $row) {

    if ($row['default_database'] === null) {
        $db = 'interlink';
    } elseif ($row['default_database'] === 'unknown_db') {
        $db = 'interlink';
    } else {
        $db = $row['default_database'];
    }
    $mysqli->select_db($db);

    print_r($row['id']);

    $statement = interference($row['select_query'], $mysqli, $db);
    if ($statement[0] === 'wrong db') {
        $db = 'amortization';
        $mysqli->select_db($db);
        $statement = interference($row['select_query'], $mysqli, $db);
        if ($statement[0] === 'wrong db') {
            $db = 'mykidbiz';
            $mysqli->select_db($db);
            $statement = interference($row['select_query'], $mysqli, $db);
            if ($statement[0] === 'wrong db') {
                $db = 'tech';
                $mysqli->select_db($db);
                $statement = interference($row['select_query'], $mysqli, $db);
                if ($statement[0] === 'wrong db') {
                    $db = 'billing';
                    $mysqli->select_db($db);
                    $statement = interference($row['select_query'], $mysqli, $db);
                    if ($statement[0] === 'wrong db') {
                        $db = 'commissions';
                        $mysqli->select_db($db);
                        $statement = interference($row['select_query'], $mysqli, $db);
                    }
                    if ($statement[0] === 'wrong db') {
                        $db = 'unknown_db';
                    }
                }
            }
        }
    }

    $query = "UPDATE sql_table SET create_columns=:create_columns,skip=1,default_database=:db WHERE id=:id";
    $sql = $my_conn->prepare($query);

    $sql->bindValue(':create_columns', $statement[1]);
    $sql->bindValue(':db', $db);
    $sql->bindParam(':id', $row['id'], PDO::PARAM_INT);

    // print_r($statement . PHP_EOL . PHP_EOL);

    if ($sql->execute()) {
        echo " Successfully updated record " . PHP_EOL;
    } else {
        print_r($sql->errorInfo()); // if any error is there it will be posted
        $msg = " Database problem, please contact site admin ";
    }
}

$mysqli->close();
