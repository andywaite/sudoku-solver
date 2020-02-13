<?php

namespace Andywaite\Sudoku;

class CellChecker
{
    /**
     * @param Grid $grid
     * @param int $x
     * @param int $y
     * @param $value
     * @return bool
     */
    public function isValidMove(Grid $grid, int $x, int $y, $value): bool
    {
        // Check for same value in X
        for ($i = 0; $i < 9; $i++) {
            if ($grid->getValue($x, $i) == $value) {
                return false;
            }
        }

        // Check for same value in Y
        for ($i = 0; $i < 9; $i++) {
            if ($grid->getValue($i, $y) == $value) {
                return false;
            }
        }

        // Check for same value in segment
        $segmentXMin = $x - ($x%3);
        $segmentXMax = $segmentXMin + 2;

        $segmentYMin = $y - ($y%3);
        $segmentYMax = $segmentYMin + 2;

        for ($i = $segmentXMin; $i <= $segmentXMax; $i++) {
            for ($n = $segmentYMin; $n <= $segmentYMax; $n++) {
                if ($grid->getValue($i, $n) == $value) {
                    return false;
                }
            }
        }

        return true;
    }
}
