<?php

namespace Andywaite\Sudoku;

/**
 * Class Solver
 * @package Andywaite\Sudoku
 *
 * Solves a Sudoku puzzle!
 */
class RecursiveSolverWithOptimisation implements Solver
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
        $peers = [];
        $obviousMoves = [];

        // Try searching around the last move to save time
        if (!empty($lastX) && !empty($lastY)) {
            $peers = $this->cellChecker->getPeers($lastX, $lastY);

            foreach ($peers as $peer) {
                // If it's not empty ignore it
                if (!$grid->isEmpty($peer['x'], $peer['y'])) {
                    continue;
                }

                // Get moves for this square
                $candidates = $this->cellChecker->getCandidates($grid, $peer['x'], $peer['y']);
                $candidateCount = count($candidates);

                // Oops, dead end situation - no point carrying on
                if ($candidateCount === 0) {
                    return null;
                }

                // Only one valid candidate - let's make a move!
                if ($candidateCount === 1) {
                    $obviousMoves[] = [
                        'x' => $peer['x'],
                        'y' => $peer['y'],
                        'value' => $candidates[0]
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

        foreach ($empty as $cell) {
            $x = $cell['x'];
            $y = $cell['y'];

            // Skip if already checked
            if (isset($peers[$x . $y])) {
                continue;
            }

            $candidates = $this->cellChecker->getCandidates($grid, $x, $y);
            $candidateCount = count($candidates);

            // Oops, dead end situation - no point carrying on
            if ($candidateCount === 0) {
                return null;
            }

            // Only one valid candidate - let's make a move!
            if ($candidateCount === 1) {
                $obviousMoves[] = [
                    'x' => $x,
                    'y' => $y,
                    'value' => $candidates[0]
                ];
            }
        }

        return $obviousMoves;
    }

    /**
     * @param Grid $grid
     * @return bool
     * @throws \Exception
     */
    public function solve(Grid $grid): bool
    {
        return $this->recursiveSolve($grid);
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
    public function recursiveSolve(Grid $grid, ?int $lastX = null, ?int $lastY = null): bool
    {
        // METHOD 1 - fill in any cells where there's only one option
        $candidates = $this->getObviousMoves($grid, $lastX, $lastY);

        // getObviousMoves returns null if there is ANY square where nothing fits - helps avoid spending any more time in dead ends
        if ($candidates === null) {
            return false;
        }

        // If we have any obvious moves, let's make them!
        if (count($candidates)) {

            $candidatesSoFar = [];

            // For any places where only one option works, make it
            foreach ($candidates as $candidate) {
                if ($this->cellChecker->isValidMove($grid, $candidate['x'], $candidate['y'], $candidate['value'])) {
                    $grid->setValue($candidate['x'], $candidate['y'], $candidate['value']);
                    $candidatesSoFar[] = $candidate;
                } else {
                    // Collision of obvious moves, must have taken a wrong turn earlier on so undo everything
                    foreach ($candidatesSoFar as $candidateSoFar) {
                        $grid->nullValue($candidateSoFar['x'], $candidateSoFar['y']);
                    }
                    // Backtrack
                    return false;
                }
            }

            // Recursively solve
            $solve = $this->recursiveSolve($grid);

            // Even though this HAS to be right, a previous brute force move may have been wrong, so we may need to backtrack
            if (!$solve) {
                // Undo all moves and pass up the chain we were wrong :(
                foreach ($candidates as $candidate) {
                    $grid->nullValue($candidate['x'], $candidate['y']);
                }
                // Backtrack
                return false;
            }

            // We won!
            return true;
        }

        // METHOD 2 - brute force by trying something that could fit
        $emptyCells = $grid->getEmptyCells();
        $optimalCell = [
            'moveCount' => 10
        ];

        // Find out what valid moves we have for each empty cell
        foreach ($emptyCells as $id => $emptyCell) {
            $candidates = $this->cellChecker->getCandidates($grid, $emptyCell['x'], $emptyCell['y']);
            $candidateCount = count($candidates);

            // If there's any cell we can't do anything with, we hit a dead end clearly
            if ($candidateCount == 0) {
                return false;
            }

            // We're looking for a move with fewest possible options - speeds things up
            if ($candidateCount < $optimalCell['moveCount']) {
                $optimalCell = $emptyCell;
                $optimalCell['moveCount'] = $candidateCount;
                $optimalCell['moves'] = $candidates;
            }
        }

        // If there is a move to be made, let's make it!
        if (isset($optimalCell['moves'])) {
            $x = $optimalCell['x'];
            $y = $optimalCell['y'];

            $validMoves = $optimalCell['moves'];

            // Loop through possible values
            foreach ($validMoves as $try) {
                // Set value
                $grid->setValue($x, $y, $try);

                // Recursively solve
                if ($this->recursiveSolve($grid, $x, $y)) {
                    // Yay, solved!
                    return true;
                }

                // Must have failed, backtrack for this cell and try next
                $grid->nullValue($x, $y);
            }
            // Must be in a bad branch, need to backtrack :(
            return false;
        }

        // Winner winner chicken dinner - nothing left to complete so we must be finished!
        return true;
    }
}
