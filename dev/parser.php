<?php

require_once __DIR__ . '/../vendor/autoload.php';

if ($argc != 2) {
    echo 'Usage: parser.php file.mep' . PHP_EOL;
    exit(1);
}

$compiler = Hoa\Compiler\Llk\Llk::load(new Hoa\File\Read(__DIR__ . '/../src/MessageEventProtocol.pp'));

// 2. Parse a data.
$ast = $compiler->parse(file_get_contents($argv[1]));

// 3. Dump the AST.
$dump = new Hoa\Compiler\Visitor\Dump();

echo $dump->visit($ast);
