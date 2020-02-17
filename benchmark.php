<?php

include 'vendor/autoload.php';

$examples = [
    'examples/hard.txt',
    'examples/harder.txt',
    'examples/hardest.txt',
    'examples/evenharder.txt'
];

// Create our classes
$printer = new \Andywaite\Sudoku\GridPrinter();
$gridLoader = new \Andywaite\Sudoku\GridLoader();
$cellChecker = new \Andywaite\Sudoku\CellChecker();

$solvers = [
    "Basic Recursive" => new \Andywaite\Sudoku\BasicRecursiveSolver($cellChecker),
    "Optimised Recursive" => new \Andywaite\Sudoku\RecursiveSolverWithOptimisation($cellChecker),
    "Hacky But Fast" => new \Andywaite\Sudoku\HackyButFast()
];

$results = [];

foreach ($examples as $example) {

    foreach ($solvers as $title => $solver) {
        // Load start grid from file
        $grid = new \Andywaite\Sudoku\Grid();
        $gridLoader->loadGrid($grid, $example);

        // Solve!
        $start = microtime(true);
        try {
            if (!$solver->solve($grid)) {
                throw new Exception("Solver returned false");
            }
            $end = microtime(true);
            $runTime = ($end - $start);
        } catch (Exception $e) {
            $runTime = "FAIL";
        }

        $results[$example][$title] =number_format($runTime, 6);
    }
}

$benchmarkOutput = "# Benchmarks for solvers";
$benchmarkOutput .= "\n| |";
$blankLine = "\n|---|";

foreach ($solvers as $title => $solver) {
    $benchmarkOutput .= $title."|";
    $blankLine .= "---|";
}

$benchmarkOutput .= $blankLine;

foreach($results as $exampleFilename => $result) {
    $benchmarkOutput .= "\n|".$exampleFilename."|".implode("|", $result)."|";
}

file_put_contents("benchmark.md", $benchmarkOutput);
