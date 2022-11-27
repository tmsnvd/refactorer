<?php

require_once '../vendor/autoload.php';

use erguncaner\Table\Table;
use erguncaner\Table\TableColumn;
use erguncaner\Table\TableRow;
use erguncaner\Table\TableCell;
use PhpMyAdmin\SqlParser\Utils\Formatter;

$table = new Table();
$table->addColumn('nr', new TableColumn('nr'));
$table->addColumn('file_line', new TableColumn('file:line'));
$table->addColumn('original_query', new TableColumn('original_query'));
$table->addColumn('create_query', new TableColumn('create_query query'));
$table->addColumn('create_columns', new TableColumn('new create_columns'));
$table->addColumn('select_query', new TableColumn('select_query'));

$my_conn = new PDO('sqlite:../q.db');
$my_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


$sql = "SELECT * FROM sql_table";
foreach ($my_conn->query($sql) as $data) {
    $cells = [
        'nr' => new TableCell($data['id']),
        'file_line' => new TableCell($data['file'] . ':' . $data['line']),
        'original_query' => new TableCell(
            Formatter::format($data['original_query'] ?? '', ['type' => 'html']),
            ['style' => 'border-bottom: solid 1px black;']
        ),
        'create_query' => new TableCell(
            Formatter::format($data['create_query'] ?? '', ['type' => 'html']),
            ['style' => 'border-bottom: solid 1px black;']
        ),
        'create_columns' => new TableCell(
            Formatter::format($data['create_columns'] ?? '', ['type' => 'html']),
            ['style' => 'border-bottom: solid 1px black;']
        ),
        'select_query' => new TableCell(
            Formatter::format($data['select_query'] ?? '', ['type' => 'html']),
            ['style' => 'border-bottom: solid 1px black;']
        ),
    ];

    $table->addRow(new TableRow($cells));
}

file_put_contents('out.html', $table->html());
