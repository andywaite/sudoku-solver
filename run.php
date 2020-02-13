<?php

include 'vendor/autoload.php';

$grid = new \Andywaite\Sudoku\Grid();

// Seed data
$grid->setValue(0, 0, 5);
$grid->setValue(1, 0, 3);
$grid->setValue(4, 0, 7);
$grid->setValue(0, 1, 6);
$grid->setValue(3, 1, 1);
$grid->setValue(4, 1, 9);
$grid->setValue(5, 1, 5);
$grid->setValue(1, 2, 9);
$grid->setValue(2, 2, 8);
$grid->setValue(7, 2, 6);
$grid->setValue(0, 3, 8);
$grid->setValue(4, 3, 6);
$grid->setValue(8, 3, 3);
$grid->setValue(0, 4, 4);
$grid->setValue(3, 4, 8);
$grid->setValue(5, 4, 3);
$grid->setValue(8, 4, 1);
$grid->setValue(0, 5, 7);
$grid->setValue(4, 5, 2);
$grid->setValue(8, 5, 6);
$grid->setValue(1, 6, 6);
$grid->setValue(6, 6, 2);
$grid->setValue(7, 6, 8);
$grid->setValue(3, 7, 4);
$grid->setValue(4, 7, 1);
$grid->setValue(5, 7, 9);
$grid->setValue(8, 7, 5);
$grid->setValue(4, 8, 8);
$grid->setValue(7, 8, 7);
$grid->setValue(8, 8, 9);

echo "\nUnsolved";
$printer = new \Andywaite\Sudoku\GridPrinter();
$printer->printGrid($grid);

$solver = new \Andywaite\Sudoku\Solver(new \Andywaite\Sudoku\CellChecker());
$solver->solve($grid);

echo "\n\nSolved";
$printer->printGrid($grid);
