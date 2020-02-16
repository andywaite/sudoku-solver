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
    protected function getObviousMoves(Grid $grid, ?int $lastX = null, ?int $lastY = null): ?array
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
                $moveCount = count($moves);

                // Oops, dead end situation - no point carrying on
                if ($moveCount === 0) {
                    return null;
                }

                // Only one valid move - let's make it!
                if ($moveCount === 1) {
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
            $moveCount = count($moves);

            // Oops, dead end situation - no point carrying on
            if ($moveCount === 0) {
                return null;
            }

            // Only one valid move - let's make it!
            if ($moveCount === 1) {
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
        // METHOD 1 - fill in any cells where there's only one option
        $moves = $this->getObviousMoves($grid, $lastX, $lastY);

        // getObviousMoves returns null if there is ANY square where nothing fits - helps avoid spending any more time in dead ends
        if ($moves === null) {
            return false;
        }

        // If we have any obvious moves, let's make them!
        if (count($moves)) {

            $movesSoFar = [];

            // For any places where only one option works, make it
            foreach ($moves as $move) {
                if ($this->cellChecker->isValidMove($grid, $move['x'], $move['y'], $move['value'])) {
                    $grid->setValue($move['x'], $move['y'], $move['value']);
                    $movesSoFar[] = $move;
                } else {
                    // Collision of obvious moves, must have taken a wrong turn earlier on so undo everything
                    foreach ($movesSoFar as $moveSoFar) {
                        $grid->nullValue($moveSoFar['x'], $moveSoFar['y']);
                    }
                    // Backtrack
                    return false;
                }
            }

            // Recursively solve
            $solve = $this->solve($grid);

            // Even though this HAS to be right, a previous brute force move may have been wrong, so we may need to backtrack
            if (!$solve) {
                // Undo all moves and pass up the chain we were wrong :(
                foreach ($moves as $move) {
                    $grid->nullValue($move['x'], $move['y']);
                }
                // Backtrack
                return false;
            }

            // We won!
            return true;
        }

        // METHOD 2 - brute force by trying something that could fit
        $emptyCells = $grid->getEmptyCells();

        $sortedCells = [];

        // Find out what valid moves we have for each empty cell
        foreach ($emptyCells as $id => $emptyCell) {
            $x = $emptyCell['x'];
            $y = $emptyCell['y'];
            $potentialMoves = $this->cellChecker->getValidMoves($grid, $x, $y);
            $moveCount = count($potentialMoves);

            // If there's any cell we can't go, we hit a dead end clearly
            if ($moveCount == 0) {
                return false;
            }

            $emptyCell['moves'] = $potentialMoves;
            $sortedCells[$moveCount.$x.$y] = $emptyCell;
        }

        // We want to try the cell with the fewest options first, so sort. Expensive op, but pays for itself on complex puzzles
        ksort($sortedCells);

        // Loop cols
        foreach ($sortedCells as $cell) {

            $x = $cell['x'];
            $y = $cell['y'];

            $validMoves = $cell['moves'];

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

            // Backtrack
            return false;
        }

        // We won - nowhere left to go!
        return true;
    }
}
