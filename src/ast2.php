<?php

require_once '../vendor/autoload.php';

use Microsoft\PhpParser\{DiagnosticsProvider, Node, Parser, PositionUtilities, TextEdit};

// Instantiate new parser instance
$parser = new Parser();

// Return and print an AST from string contents
$my_conn = new PDO('sqlite:../q.db');
$my_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "SELECT file, GROUP_CONCAT(line) as lines, GROUP_CONCAT(id) as ids FROM sql_table GROUP BY file;";

foreach ($my_conn->query($sql) as $row) {
    $code = file_get_contents('/Users/Tomas.Neverdauskas@mheducation.com/dev/interlink/' . $row['file']);
    $astNode = $parser->parseSourceFile($code);
    // var_dump($astNode);

    echo $row['file'] . PHP_EOL;

    $lines = explode(',', $row['lines']);
    $ids = explode(',', $row['ids']);

    foreach ($lines as $key => $line) {

        echo $line . ' -> ';
        echo $ids[$key] . PHP_EOL;

        $edits = [];

        foreach ($astNode->getDescendantNodes() as $descendant) {
            if ($descendant instanceof Node\StringLiteral) {
                // Print the Node text (without whitespace or comments)

                $echoKeywordStartPosition = $descendant->getStartPosition();

                $lineCharacterPosition = PositionUtilities::getLineCharacterPositionFromPosition(
                    $echoKeywordStartPosition,
                    $descendant->getFileContents()
                );

                if ((int)$line === $lineCharacterPosition->line + 1) {

                    echo ($lineCharacterPosition->line + 1) . PHP_EOL;

                    $assigmentPosition = PositionUtilities::getLineCharacterPositionFromPosition(
                        $descendant->getStartPosition(),
                        $descendant->getFileContents()
                    );

                    // echo ($descendant->getText()) . PHP_EOL;
                    $edits = [
                        // new TextEdit($descendant->getStartPosition() - 1, 0, PHP_EOL . '$createQuery = "' . $row['create_query'] . '"; ' . PHP_EOL . 'kbQuery($createQuery);' . PHP_EOL),
                        new TextEdit($descendant->getStartPosition() - 1, 0, '"' . $row['select_query'] . '"'),
                    ];
                }

                // All Nodes link back to their parents, so it's easy to navigate the tree.
                // $grandParent = $descendant->getParent()->getParent();
                // var_dump($grandParent->getNodeKindName());

                // The AST is fully-representative, and round-trippable to the original source.
                // This enables consumers to build reliable formatting and refactoring tools.
                // var_dump($grandParent->getLeadingCommentAndWhitespaceText());
            }
        }

        if (!empty($edits)) {
            file_put_contents('/Users/Tomas.Neverdauskas@mheducation.com/dev/interlink/' . $row['file'], TextEdit::applyEdits($edits, $code));
        }
    }


}