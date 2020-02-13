<?php

namespace Andywaite\Sudoku;

class Solver
{
    /**
     * @var CellChecker
     */
    private $cellChecker;

    public function __construct(CellChecker $cellChecker)
    {
        $this->cellChecker = $cellChecker;
    }

    /**
     * @param Grid $grid
     * @return bool
     */
    public function solve(Grid $grid): bool
    {
        // Loop cols
        for ($x = 0; $x < 9; $x++) {

            // Loop rows
            for ($y = 0; $y < 9; $y++) {

                // Check for empty cells
                if ($grid->isEmpty($x, $y)) {

                    // Loop through possible values
                    for ($try = 1; $try <= 9; $try++) {

                        // If valid move (i.e. no collision)
                        if ($this->cellChecker->isValidMove($grid, $x, $y, $try)) {

                            // Set value
                            $grid->setValue($x, $y, $try);

                            // Recursively solve
                            if ($this->solve($grid)) {
                                // Yay, solved!
                                return true;
                            }

                            // Must have failed, backtrack for this cell and try next
                            $grid->nullValue($x, $y);
                        }
                    }

                    // This didn't work, try another route
                    return false;
                }
            }
        }

        // All complete
        return true;
    }
}
