<?php

namespace Andywaite\Sudoku;

/**
 * Class GridPrinter
 * @package Andywaite\Sudoku
 *
 * Simple CLI grid printer
 */
class GridPrinter
{
    /**
     * Dumb way to visualise grid on the CLI
     *
     * @param Grid $grid
     */
    public function printGrid(Grid $grid)
    {
        for ($y = 0; $y < 9; $y++) {

            if ($y % 3 === 0) {
                echo "\n";
            }

            for ($x = 0; $x < 9; $x++) {

                if ($x % 3 === 0) {
                    echo "  ";
                }

                if ($grid->isEmpty($x, $y)) {
                    echo "[ ]";
                } else {
                    echo "[".$grid->getValue($x, $y)."]";
                }
            }

            echo "\n";
        }
    }
}
