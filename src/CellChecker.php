<?php

namespace Andywaite\Sudoku;

/**
 * Class CellChecker
 * @package Andywaite\Sudoku
 *
 * Helper class to perform mass functions across our Grid
 */
class CellChecker
{
    /**
     * @param Grid $grid
     * @param int $x
     * @param int $y
     * @return int[]
     */
    public function getValidMoves(Grid $grid, int $x, int $y): array
    {
        $movesForSquare = [];

        for ($i = 1; $i <= 9; $i++) {
            if ($this->isValidMove($grid, $x, $y, $i)) {
                $movesForSquare[] = $i;
            }
        }

        return $movesForSquare;
    }

    /**
     * Get cells affected by our last move (to check if we have any new obvious moves)
     *
     * These are keyed with the x:y to help us avoid checking the same cell twice later
     *
     * @param int $x
     * @param int $y
     * @return array
     */
    public function getAffectedCells(int $x, int $y): array
    {
        $cells = [];

        // Whole X Row and Y row
        for ($i = 0; $i < 9; $i++) {
            $cells[$i.":".$y] = [
              'x' => $i,
              'y' => $y
            ];
            $cells[$x.":".$i] = [
                'x' => $x,
                'y' => $i
            ];
        }

        // Check for same value in segment
        $segmentXMin = $x - ($x%3);
        $segmentXMax = $segmentXMin + 2;

        $segmentYMin = $y - ($y%3);
        $segmentYMax = $segmentYMin + 2;

        for ($i = $segmentXMin; $i <= $segmentXMax; $i++) {
            for ($n = $segmentYMin; $n <= $segmentYMax; $n++) {
                $cells[$i.":".$n] = [
                    'x' => $i,
                    'y' => $n
                ];
            }
        }

        return $cells;
    }

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
