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
     * Look for a place where only one value will work
     *
     * @param Grid $grid
     * @param int|null $lastX
     * @param int|null $lastY
     * @return array|null
     */
    protected function getObviousMove(Grid $grid, ?int $lastX = null, ?int $lastY = null): ?array
    {
        $affectedCells = [];

        // Try searching around the last move to save time
        if (!empty($lastX) && !empty($lastY)) {
            $affectedCells = $this->cellChecker->getAffectedCells($lastX, $lastY);

            foreach ($affectedCells as $affectedCell) {
                if ($grid->isEmpty($affectedCell['x'], $affectedCell['y'])) {
                    $moves = $this->cellChecker->getValidMoves($grid, $affectedCell['x'], $affectedCell['y']);

                    // Only one valid move - let's make it!
                    if (count($moves) === 1) {
                        return [
                            'x' => $affectedCell['x'],
                            'y' => $affectedCell['y'],
                            'value' => $moves[0]
                        ];
                    }
                }
            }
        }

        // OK, maybe we need to look further afield

        // Loop all cols
        for ($x = 0; $x < 9; $x++) {
            // Loop all rows
            for ($y = 0; $y < 9; $y++) {

                // Skip if already checked
                if (isset($affectedCells[$x.":".$y])) {
                    continue;
                }

                if ($grid->isEmpty($x, $y)) {

                    $moves = $this->cellChecker->getValidMoves($grid, $x, $y);

                    // Only one valid move - let's make it!
                    if (count($moves) === 1) {
                        return [
                            'x' => $x,
                            'y' => $y,
                            'value' => $moves[0]
                        ];
                    }
                }
            }
        }

        return null;
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
        // Before we brute force, try and see if there's a place where there's only one option. This reduces execution on a hard puzzle from ~6s to ~0.5s but possibly makes easy puzzles take slightly longer?
        if ($move = $this->getObviousMove($grid, $lastX, $lastY)) {

            // If there is a place where only one option works, make it
            $grid->setValue($move['x'], $move['y'], $move['value']);

            // Recursively solve
            $solve = $this->solve($grid, $move['x'], $move['y']);

            // Even though this HAS to be right, a previous brute force move may have been wrong, so we may need to backtrack
            if (!$solve) {
                // Undo and pass up the chain we were wrong :(
                $grid->nullValue($move['x'], $move['y']);
                return false;
            }

            // We won!
            return true;
        }

        // Now brute force

        // Loop cols
        for ($x = 0; $x < 9; $x++) {

            // Loop rows
            for ($y = 0; $y < 9; $y++) {

                // Check if this is empty cell
                if (!$grid->isEmpty($x, $y)) {
                    // Next cell
                    continue;
                }

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

                // This didn't work, try another route
                return false;
            }
        }

        // All complete
        return true;
    }
}
