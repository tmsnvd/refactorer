<?php

require_once '../vendor/autoload.php';

use Doctrine\SqlFormatter\HtmlHighlighter;
use Doctrine\SqlFormatter\SqlFormatter;
use PHPSQLParser\PHPSQLParser;
use erguncaner\Table\Table;
use erguncaner\Table\TableColumn;
use erguncaner\Table\TableRow;
use erguncaner\Table\TableCell;


$data = explode('||linesplit||', file_get_contents('../../interlink/out.file'));
$parser = new PHPSQLParser();

$table = new Table();
$table->addColumn('nr', new TableColumn('nr'));
$table->addColumn('file_line', new TableColumn('file:line'));
$table->addColumn('query', new TableColumn('query'));
$table->addColumn('splited_query', new TableColumn('new query'));
$table->addColumn('comment', new TableColumn('comment'));

$sqlFormatter = new SqlFormatter(new HtmlHighlighter([], true));
$i = 1;
foreach ($data as $line) {
    $query = explode('||innersplit||', $line);
    if (trim($query[0]) === '') {
        continue;
    }

    $comment = '';
    if (str_contains(trim($query[2]), 'interlink_prior_year_changes')) {
        $comment = 'skip';
    }

    $queryPure = trim(trim($query[2]), '"');
    $isTemp = refactor($parser->parse($queryPure));
    if ($isTemp) {
        continue;
    }

    $cells = [
        'nr' => new TableCell($i++),
        'file_line' => new TableCell(trim($query[0]).':'.trim($query[1])),
        'query' => new TableCell($sqlFormatter->format($queryPure, false)),
        'splited_query' => new TableCell(''),
        'comment' => new TableCell($comment),
    ];

    $table->addRow(new TableRow($cells));
}

echo $table->html();
// file_put_contents('out.html', $table->html());

function refactor($parsed)
{
    $createTable = null;
    $simpleCase = false;
    $isTemp = false;

    foreach ($parsed as $name => $part) {
        if ($name === 'CREATE') {
            if ($part['base_expr'] == 'TEMPORARY TABLE') {
                $isTemp = true;
            }
            $createTable['CREATE'] = array_shift($parsed);
            $createTable['TABLE'] = array_shift($parsed);
        }
    }

    if (isset($parsed['FROM']) && count($parsed['FROM']) === 1) {
        $simpleCase = true;
    }

    if ($simpleCase) {
        // print_r($createTable);
    }

    return $isTemp;
}

// $mysqli = new mysqli("localhost", "my_user", "my_password", "world");

function interference(string $query)
{
    if (mysqli_connect_errno()) {
        printf("Connect failed: %s\n", mysqli_connect_error());
        exit();
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

// $mysqli->close();
