<?php

namespace Andywaite\Sudoku;

/**
 * Class Solver
 * @package Andywaite\Sudoku
 *
 * Solves a Sudoku puzzle!
 */
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
     * Look for places where only one value will work
     *
     * @param Grid $grid
     * @param int|null $lastX
     * @param int|null $lastY
     * @return array
     */
    protected function getObviousMoves(Grid $grid, ?int $lastX = null, ?int $lastY = null): array
    {
        $affectedCells = [];
        $obviousMoves = [];

        // Try searching around the last move to save time
        if (!empty($lastX) && !empty($lastY)) {
            $affectedCells = $this->cellChecker->getAffectedCells($lastX, $lastY);

            foreach ($affectedCells as $affectedCell) {
                // If it's not empty ignore it
                if (!$grid->isEmpty($affectedCell['x'], $affectedCell['y'])) {
                    continue;
                }

                // Get moves for this square
                $moves = $this->cellChecker->getValidMoves($grid, $affectedCell['x'], $affectedCell['y']);

                // Only one valid move - let's make it!
                if (count($moves) === 1) {
                    $obviousMoves[] = [
                        'x' => $affectedCell['x'],
                        'y' => $affectedCell['y'],
                        'value' => $moves[0]
                    ];
                }
            }
        }

        // If we have some moves, make them!
        if (count($obviousMoves) > 0) {
            return $obviousMoves;
        }

        // OK, maybe we need to look further afield

        // Loop empty cells
        $empty = $grid->getEmptyCells();

        foreach ($empty as $cell)
        {
            $x = $cell['x'];
            $y = $cell['y'];

            // Skip if already checked
            if (isset($affectedCells[$x.$y])) {
                continue;
            }

            $moves = $this->cellChecker->getValidMoves($grid, $x, $y);

            // Only one valid move - let's make it!
            if (count($moves) === 1) {
                $obviousMoves[] =  [
                    'x' => $x,
                    'y' => $y,
                    'value' => $moves[0]
                ];
            }
        }

        return $obviousMoves;
    }


    /**
     * Attempt to solve a Sudoku puzzle
     *
     * @param Grid $grid
     * @param int|null $lastX
     * @param int|null $lastY
     * @return bool
     * @throws \Exception
     */
    public function solve(Grid $grid, ?int $lastX = null, ?int $lastY = null): bool
    {
        // Before we brute force, try and see if there's any places where there's only one option. This reduces execution time.
        $moves = $this->getObviousMoves($grid, $lastX, $lastY);

        if (count($moves)) {

            // For any places where only one option works, make it
            foreach ($moves as $move) {
                $grid->setValue($move['x'], $move['y'], $move['value']);
            }

            // Recursively solve
            $solve = $this->solve($grid);

            // Even though this HAS to be right, a previous brute force move may have been wrong, so we may need to backtrack
            if (!$solve) {
                // Undo all moves and pass up the chain we were wrong :(
                foreach ($moves as $move) {
                    $grid->nullValue($move['x'], $move['y']);
                }
                return false;
            }

            // We won!
            return true;
        }

        // Now brute force
        $empty = $grid->getEmptyCells();

        // Loop cols
        foreach ($empty as $cell) {

            $x = $cell['x'];
            $y = $cell['y'];

            $validMoves = $this->cellChecker->getValidMoves($grid, $x, $y);

            // Loop through possible values
            foreach ($validMoves as $try) {
                // Set value
                $grid->setValue($x, $y, $try);

                // Recursively solve
                if ($this->solve($grid, $x, $y)) {
                    // Yay, solved!
                    return true;
                }


                // Must have failed, backtrack for this cell and try next
                $grid->nullValue($x, $y);

            }

            return false;
        }

        return true;
    }
}
