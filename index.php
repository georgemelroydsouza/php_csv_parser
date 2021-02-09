<?php

include "Parser.php";

$parserObject = new Parser("input.csv", "output.csv");

$parserObject->execute(true);

echo "File has been parsed and output copied into output.csv";