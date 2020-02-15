<?php

include 'vendor/autoload.php';

/**
 * @todo load start grids from a separate json file
 */
$grids = [
    'hard1' => [
        0 => [
            0 => 8
        ],
        1 => [
            1 => 4,
            3 => 9,
            5 => 2,
            8 => 5
        ],
        2 => [
            0 => 1,
            2 => 2,
            3 => 7
        ],
        3 => [
            0 => 6,
            2 => 3,
            6 => 4
        ],
        4 => [
            2 => 9,
            3 => 5,
            4 => 4,
            5 => 8,
            6 => 6
        ],
        5 => [
            2 => 1,
            6 => 5,
            8 => 2
        ],
        6 => [
            5 => 7,
            6 => 8,
            8 => 3
        ],
        7 => [
            0 => 2,
            3 => 4,
            5 => 3,
            7 => 5
        ],
        8 => [
            8 => 7
        ]
    ]
];

// Create our classes
$grid = new \Andywaite\Sudoku\Grid();
$printer = new \Andywaite\Sudoku\GridPrinter();
$solver = new \Andywaite\Sudoku\Solver(new \Andywaite\Sudoku\CellChecker());

// Populate grid with seed data
foreach ($grids['hard1'] as $x => $ys) {
    foreach ($ys as $y => $val) {
        $grid->setValue($x, $y, $val);
    }
}

// Echo unsolved state so we can see the "before"
echo "\nUnsolved";
$printer->printGrid($grid);

// Solve!
$start = microtime(true);
try {
    $solver->solve($grid);
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
echo "\nFailed paths: ".$grid->getUndos()."\n";
echo "\nExecution: ".$runTime."\n";
