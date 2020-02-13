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
        for ($x = 0; $x < 9; $x++) {
            for ($y = 0; $y < 9; $y++) {
                if ($grid->isEmpty($x, $y)) {
                    for ($try = 1; $try <= 9; $try++) {
                        if ($this->cellChecker->isValidMove($grid, $x, $y, $try)) {
                            $grid->setValue($x, $y, $try);
                            if ($this->solve($grid)) {
                                return true;
                            }
                            $grid->nullValue($x, $y);
                        }
                    }
                    return false;
                }
            }
        }

        return true;
    }
}
