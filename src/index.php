<?php

require_once '../vendor/autoload.php';

use PhpMyAdmin\SqlParser\Exceptions\ParserException;
use PhpMyAdmin\SqlParser\Parser;

use erguncaner\Table\Table;
use erguncaner\Table\TableColumn;
use erguncaner\Table\TableRow;
use erguncaner\Table\TableCell;

// phpgrep -format '||linesplit||{{.Filename}}||innersplit||{{.Line}}||innersplit||{{.f}}' ./ '$_ = $f' 'f~(?i)[\S\s*]*create[\S\s*]*table[\S\s*]*select' 2>/dev/null 1> out.file

$data = explode('||linesplit||', file_get_contents('../../interlink/out.file'));
// $sqlCreator = new PHPSQLCreator();

$table = new Table();
$table->addColumn('nr', new TableColumn('nr'));
$table->addColumn('file_line', new TableColumn('file:line'));
$table->addColumn('query', new TableColumn('query'));
$table->addColumn('split_query', new TableColumn('new query'));
$table->addColumn('comment', new TableColumn('comment'));

$i = 1;
foreach ($data as $line) {
    $data = explode('||innersplit||', $line);
    if (trim($data[0]) === '') {
        continue;
    }

    $comment = '';
    $stringQuery = '';

    $query = trim(trim($data[2]), '"');
    try {
        $sqlParser = new Parser($query);
    } catch (ParserException $e) {
        $comment = 'exception';
    }

    if (isset($sqlParser->statements[0]) && $sqlParser->statements[0]->options) {
        /** @var $optionsArray PhpMyAdmin\SqlParser\Components\OptionsArray */
        $optionsArray = $sqlParser->statements[0]->options;
        if ($optionsArray->has('TEMPORARY')) {
            continue;
        }
    }

    $test = '';
    if ($sqlParser->statements[0]->select) {
        $test = $sqlParser->statements[0]->select->build();
    }

    $cells = [
        'nr' => new TableCell($i++),
        'file_line' => new TableCell(trim($data[0]).':'.trim($data[1]), ['style' => 'font-size: 9px;']),
        'query' => new TableCell(
            PhpMyAdmin\SqlParser\Utils\Formatter::format($query, ['type' => 'html']),
            ['style' => 'border-bottom: solid 1px blue;']
        ),
        'split_query' => new TableCell(PhpMyAdmin\SqlParser\Utils\Formatter::format($test, ['type' => 'html'])),
        'comment' => new TableCell($comment),
    ];

    $table->addRow(new TableRow($cells));
}

// echo $table->html();
file_put_contents('out.html', $table->html());

function refactor($parsed)
{
    $createTable = null;
    $simpleCase = false;
    $isTemp = false;


    if (isset($parsed['FROM']) && count($parsed['FROM']) === 1) {
        $simpleCase = true;
    }


    return $isTemp;
}
