<?php

require_once '../vendor/autoload.php';

use Microsoft\PhpParser\{DiagnosticsProvider,
    Node,
    Node\Expression\AssignmentExpression,
    Node\Expression\BinaryExpression,
    Parser,
    PositionUtilities,
    TextEdit
};

$parser = new Parser();

$files = [
    '../test.php'
];

$my_conn = new PDO('sqlite:../q.db');
$my_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "SELECT file, GROUP_CONCAT(line) as lines, GROUP_CONCAT(id) as ids FROM sql_table WHERE uses_temp_table = 0 AND very_slow_query = 0 GROUP BY file";

foreach ($my_conn->query($sql) as $row) {
//foreach ($files as $file) {
    //$code = file_get_contents($file);
    $file = '/Users/Tomas.Neverdauskas@mheducation.com/dev/interlink/' . $row['file'];
    $code = file_get_contents($file);
    $astNode = $parser->parseSourceFile($code);
    $edits = [];

    echo $row['file'] . PHP_EOL;

    $lines = explode(',', $row['lines']);
    $ids = explode(',', $row['ids']);

    foreach ($lines as $key => $line) {

        $id = $ids[$key];

        $sql2 = "SELECT * FROM sql_table WHERE id=$id";
        $result = $my_conn->query($sql2);
        $row = $result->fetch(PDO::FETCH_OBJ);

        foreach ($astNode->getDescendantNodes() as $sqlQueryStatement) {
            if ($sqlQueryStatement instanceof AssignmentExpression) {
                $string = $sqlQueryStatement->rightOperand;
                $i = 0;

                if (!$string instanceof Node\StringLiteral) {
                    if (!$string instanceof BinaryExpression) {
                        continue;
                    } else {
                        $i = 1;
                    }
                }

                if (str_contains($string->getText(), "/**{$row->line}**/")) {
                    $found = "yes";
                } else {
                    continue;
                }

                $lineCharacterPosition = PositionUtilities::getLineCharacterPositionFromPosition(
                    $sqlQueryStatement->getStartPosition(),
                    $sqlQueryStatement->getFileContents()
                );

                if ($lineCharacterPosition->character - 1 > 0) {
                    $fromLeft = str_repeat(' ', $lineCharacterPosition->character);
                } else {
                    $fromLeft = '';
                }

                $edits[] = new TextEdit($sqlQueryStatement->getStartPosition(), 0, '$createTable = "' . $row->create_query . '"; ' . PHP_EOL . $fromLeft . 'kb_query($createTable);' . PHP_EOL . PHP_EOL .$fromLeft);
            }
        }
    }

    if (!empty($edits)) {
        file_put_contents($file, TextEdit::applyEdits($edits, $code));
    }
}
