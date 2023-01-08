<?php

require_once '../vendor/autoload.php';

use PhpParser\{Lexer, Node, NodeTraverser, NodeVisitor\ParentConnectingVisitor, Parser, PrettyPrinter};
use PhpParser\NodeVisitorAbstract;

$files = [
    "commissions/scripts/generate_sales_comp_data.php" => "173",
    "cron/daily/expiring_orders.php" => "13",
    "cron/daily/new_orders_yest.php" => "20,39,59",
    "cron/daily/new_po_pending_orders.php" => "13,101",
    "cron/daily/update_amortization_prior_years.php" => "199",
    "cron/dallas_isd_training.php" => "10,20,33,46,64",
    "cron/il_sync/orders.php" => "184,290,365",
    "cron/ls_breakdown.php" => "13,31",
    "cron/monthly/renewals.php" => "126,135,176,207,279,311,326,400,412,431,449,552,570,638,695,709,838,907,939,988,1051,1059,1070,1076,1130,1148,1175,1230,1316,1436,1466,1552,1621,1644,1705,1754,1795,1870,2078,2179,2215,2284,2296,2505,2590,2655,2700,2805,2877,2892,2925,3054,3089",
    "cron/ns_sync/accounts_sync.php" => "202,210",
    "cron/ns_sync/archived/pre_release_orders_sync.php" => "20,60",
    "cron/ns_sync/archived/release_orders_sync.php" => "20,60",
    "cron/ns_sync/orders_sync.php" => "22,63",
    "cron/pro_spk_ytd_mc_fields.php" => "13,25,52,117,130",
    "cron/sa_products.php" => "22,43,62,89",
    "cron/summary/cancels.php" => "66",
    "cron/summary/new_renew.php" => "17,91",
    "cron/summary/populate_tables.inc" => "89,105,128,192,210,238,300,316,358,425,527,553,586,611,638,659,678,727,762,771,824,858,881,888,904,952,973,1012,1061,1086,1117,1137,1162,1185,1207,1239,1265,1297,1310,1350,1383,1414,1459,1477,1753,1776,1895,1919,1941,1955,2266,2377,2408,2425,2451,2461,2482,2494,2515,2567,2618,2679,2732,2947,2991,3038,3121,3140,3186",
    "cron/trainings_num_attendees.php" => "10",
    "cron/update_account_ns_id.php" => "19",
    "cron/usage_report.php" => "474,490,506,547,560,572,588,603,641,677,707,727,745,768,811,826,848,884,933,1016,1154,1189,1221,1241,1273,1303,1366,1382,1409,1502,1532,1566,1583,1619,1633,1653,1678,1697,1725,1744,1774,1812,1841,1866,1896,1919,1949,1975,2052,2123,2150,2164,2193,2229,2266,2294,2340,2366,2389,2566,2585,2610,2717,2734,2758,2967,2988,3020,3039,3065,3088,3123,3149,3177,3209,3273,3317,3343,3377,3475,3492,3513,3553,3577",
    "cron/weekly/smartyants_report.php" => "11,36,46,62,81,99,118,138,150,165",
    "finance/after_import.php" => "38,74,91",
    "finance/amortization/wizard/generate/generate_commissions_data.inc" => "7,20,44,72,139,161,200",
    "finance/amortization/wizard/generate/generate_commissions_report.inc" => "32,59,70,82,93,104,117,128,143,201,204",
    "finance/amortization/wizard/generate/generate_data.inc" => "142,156,169,188,236,250,267,276,291,367,400,411,477,486,503,524,650,735,903,1174,1207,1256,1273,1282,1303,1345,1401,1428,1492,1635,1668,1728,1803,1982,2009,2020,2035,2073,2287,2372,2466,2495,2511,2866,2956,3027,3048,3070,3205,3272,3293",
    "finance/amortization/wizard/generate/generate_report.inc" => "49,72,87,102,115,124,137,153,172,191,218,230,260,302,312,323,344,403,417,427,446,465,481,503,916,948,967,987,1006,1186",
    "finance/invoices/import/after_import_summary.php" => "68,130",
    "finance/invoices/import/import_do.php" => "161",
    "finance/invoices/import/insert_invoices_do.php" => "74",
    "finance/invoices/import/om/invoice_schedule.php" => "25,697,1058,1081,1220",
    "finance/payments/import/insert_payments_do.php" => "140,150,166,175,197,231,246,260",
    "orders/convert_pd.php" => "103",
    "reports/commissions/combined/bulk_generate/index.php" => "41,53",
    "reports/deliverables/index.php" => "327,367,384,422,464,600,630,638,650,759,769,937,977,1030,1062,1151,1335",
    "reports/finance/annual_retention/annual_retention_index.php" => "60",
    "reports/finance/ay_product_line_values/generate_query_data.inc" => "17,35,94,106,171,427",
    "reports/finance/booking_totals.php" => "181,255,389,474,488,555,614,629,1101,1170,1251,1300,1312",
    "reports/finance/change_notification/generate_data.php" => "41,55,74,97,132,148,164,176,190,203,233",
    "reports/finance/pd_data/index.php" => "60,78,91,125,151,180,215",
    "reports/iplans/index.php" => "173",
    "reports/opportunities/top_20_index.php" => "268",
    "reports/qcpr/renewals/generate_data.php" => "101,153,172,280,571",
    "reports/qcpr/renewals/generate_data_2018.php" => "121,167,180,211,235,252,348,568,588",
    "reports/renewal/candidate_index.php" => "207,233,257,275,299,380,397,417,467,517,541,556,590,631,641,665,696,722,737,753,764,871,884,895,922,938,950,966,992,1024,1040,1061",
    "reports/renewal/discount_review.php" => "147,169,222,239,265,289,307,347,381,391,423,444,473,610,626,693,727,757,772,797,808,820,831,850,870,935,945,1026,1053,1090",
    "reports/renewal/index.php" => "100,136,185,209,227,269,304,314,346,367,396,533,549,607,634",
    "reports/renewal/renewal_book/index.php" => "41",
    "reports/sales/sales_activity_history_index.php" => "70",
    "reports/tbs/index.php" => "193,234,311,342,351,364,459",
    "util/db/pd_queries.php" => "124",
    "util/function/new_renew.php" => "629,646",
    "util/function/order_validation.php" => "22,47",
    "util/function/orders.php" => "2261",
    "util/function/product_lines.php" => "148",
];

$lexer = new PhpParser\Lexer(array(
    'usedAttributes' => array(
        'comments', 'startLine', 'endLine'
    )
));
$parser = new Parser\Php7($lexer);

$traverser = new NodeTraverser();
$traverser->addVisitor(new ParentConnectingVisitor);
$traverser->addVisitor(new class extends NodeVisitorAbstract {
    public function leaveNode(Node $node, $data = null)
    {
        if ($node instanceof Node\Expr\Variable
            && in_array($node->getAttribute('startLine'), explode(',', $data['line']), false)) {

            return $node;
        }

        /* if ($node instanceof Node\Stmt\Expression
            && $node->expr instanceof Node\Expr\Assign
            && in_array($node->expr->var->getAttribute('startLine'), explode(',', $data['line']), false)) {

            $var = new Node\Expr\Variable('createTable');
            return [
                new Node\Stmt\Expression(new Node\Expr\Assign($var, new Node\Scalar\String_($data['create_query']))),
                new Node\Stmt\Expression(new Node\Expr\FuncCall(new PhpParser\Node\Name('kbQuery'), [
                    new Node\Arg(new Node\Expr\Variable('createTable')),
                ])),
                // new Node\Stmt\Expression(new Node\Expr\Assign($node->expr->var, $node->expr->expr)),
                new Node\Stmt\Expression(new Node\Expr\Assign($node->expr->var, new Node\Scalar\String_($data['select_query']))),
            ];
        } */
    }
});

$printer = new PrettyPrinter\Standard();

// foreach ($files as $file => $lines) {

$my_conn = new PDO('sqlite:../q.db');
$my_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "select * from sql_table where combined = 1";

foreach ($my_conn->query($sql) as $row) {
    $code = file_get_contents('/Users/Tomas.Neverdauskas@mheducation.com/dev/interlink/' . $row['file']);
    $oldStmts = $parser->parse($code);
    $oldTokens = $lexer->getTokens();
    $newStmts = $traverser->traverse($oldStmts, $row);

    $edits = [
        new TextEdit(100, 0, "hello")
    ];

    file_put_contents('/Users/Tomas.Neverdauskas@mheducation.com/dev/interlink/' . $row['file'], TextEdit::applyEdits($edits, $code));
    // $newCode = $printer->printFormatPreserving($newStmts, $oldStmts, $oldTokens);
    // file_put_contents('/Users/Tomas.Neverdauskas@mheducation.com/dev/interlink/' . $row['file'], $newCode);
    break;
}
