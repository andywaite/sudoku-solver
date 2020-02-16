<?php

include 'vendor/autoload.php';


// Create our classes
$grid = new \Andywaite\Sudoku\Grid();
$printer = new \Andywaite\Sudoku\GridPrinter();
$solver = new \Andywaite\Sudoku\RecursiveSolverWithOptimisation(new \Andywaite\Sudoku\CellChecker());
$gridLoader = new \Andywaite\Sudoku\GridLoader();

// Load start grid from file
$gridLoader->loadGrid($grid, 'examples/hardest.txt');

// Echo unsolved state so we can see the "before"
echo "\nUnsolved";
$printer->printGrid($grid);

// Solve!
$start = microtime(true);
try {
    if (!$solver->solve($grid)) {
        throw new Exception("Solver returned false");
    }
    echo "\n\nSolved";
} catch (Exception $e) {
    echo "\n\nFailed to solve: ".$e->getMessage();
}

// Calculate timings
$end = microtime(true);
$runTime = ($end - $start);

// Show the completed grid
$printer->printGrid($grid);

// Show some stats
echo "\nMoves: ".$grid->getMoves();
echo "\nFailed paths: ".$grid->getUndos();
echo "\nExecution: ".$runTime."\n";
