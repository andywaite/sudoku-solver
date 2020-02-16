<?php

use Andywaite\Sudoku\CellChecker;
use Andywaite\Sudoku\GridLoader;
use Andywaite\Sudoku\BasicRecursiveSolver;
use \PHPUnit\Framework\TestCase;
use Andywaite\Sudoku\Grid;

class RecursiveSolverTest extends TestCase
{
    /**
     * @dataProvider solverDataProvider
     */
    public function testSolver(string $puzzleFilename, string $solutionFilename)
    {
        $puzzleGrid = new Grid();
        $solutionGrid = new Grid();
        $solver = new BasicRecursiveSolver(new CellChecker());
        $gridLoader = new GridLoader();

        $gridLoader->loadGrid($puzzleGrid, $puzzleFilename);
        $gridLoader->loadGrid($solutionGrid, $solutionFilename);
        $worked = $solver->solve($puzzleGrid);

        $this->assertTrue($worked);

        for ($x = 0; $x < 9; $x++) {
            for ($y = 0; $y < 9; $y++) {
                $this->assertEquals($solutionGrid->getValue($x, $y), $puzzleGrid->getValue($x, $y));
            }
        }

    }

    public function solverDataProvider()
    {
        return [
            [
                'examples/hard.txt',
                'examples/hard.solution.txt'
            ],
            [
                'examples/harder.txt',
                'examples/harder.solution.txt'
            ],
            [
                'examples/hardest.txt',
                'examples/hardest.solution.txt'
            ]
        ];
    }
}
