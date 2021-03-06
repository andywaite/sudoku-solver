<?php

use Andywaite\Sudoku\GridLoader;
use Andywaite\Sudoku\HackyButFast;
use \PHPUnit\Framework\TestCase;
use Andywaite\Sudoku\Grid;

class HackyButFastTest extends TestCase
{
    /**
     * @dataProvider solverDataProvider
     */
    public function testSolver(string $puzzleFilename, string $solutionFilename)
    {
        $puzzleGrid = new Grid();
        $solutionGrid = new Grid();
        $solver = new HackyButFast();
        $gridLoader = new GridLoader();

        $gridLoader->loadGrid($puzzleGrid, $puzzleFilename);
        $gridLoader->loadGrid($solutionGrid, $solutionFilename);
        $worked = $solver->solve($puzzleGrid);

        $this->assertTrue($worked);

        for ($x = 0; $x < 9; $x++) {
            for ($y = 0; $y < 9; $y++) {
                $this->assertEquals($solutionGrid->getValue($x, $y), $puzzleGrid->getValue($x, $y), "Value in position ".$x." ".$y." is wrong");
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
            ],
            [
                'examples/evenharder.txt',
                'examples/evenharder.solution.txt'
            ]
        ];
    }
}
