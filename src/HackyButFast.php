<?php

namespace Andywaite\Sudoku;

/**
 * Horrible solution - trading maintainability and elegance for SPEED
 *
 * @package Andywaite\Sudoku
 *
 * Solves a Sudoku puzzle!
 */
class HackyButFast implements Solver
{

    /**
     * @var array
     */
    protected $peers = [];

    /**
     * @var array
     */
    protected $grid;

    public function __construct()
    {
        $this->setupPeers();
    }

    /**
     * Maintains a list of all linked cells so that when we make 1 move, we can quickly see if that makes other moves possible
     */
    protected function setupPeers()
    {
        for ($x = 0; $x < 9; $x++) {
            for ($y = 0; $y < 9; $y++) {
                $cells = [];

                // Whole X Row and Y row
                for ($i = 0; $i < 9; $i++) {

                    if ($i != $x) {
                        $cells[] = [
                            $i,
                            $y
                        ];
                    }

                    if ($i != $y) {
                        $cells[] = [
                            $x,
                            $i
                        ];
                    }
                }

                // Check for same value in segment
                $segmentXMin = $x - ($x%3);
                $segmentXMax = $segmentXMin + 2;

                $segmentYMin = $y - ($y%3);
                $segmentYMax = $segmentYMin + 2;

                for ($i = $segmentXMin; $i <= $segmentXMax; $i++) {
                    for ($n = $segmentYMin; $n <= $segmentYMax; $n++) {
                        if ($x != $i && $y != $n) {
                            $cells[] = [
                                $i,
                                $n
                            ];
                        }
                    }
                }

                $this->peers[$x][$y] = $cells;
            }
        }
    }

    /**
     * @param int $x
     * @param int $y
     * @return int[]
     */
    protected function getCandidates(int $x, int $y): array
    {
        $candidates = [
           1, 2, 3, 4, 5, 6, 7, 8, 9
        ];

        foreach ($this->peers[$x][$y] as $peer) {
            if (!isset($this->grid[$peer[0]][$peer[1]])) {
                continue;
            }

            unset($candidates[$this->grid[$peer[0]][$peer[1]]- 1]);
        }

        return array_values($candidates);
    }

    /**
     * @param Grid $grid
     * @return bool
     * @throws \Exception
     */
    public function solve(Grid $grid): bool
    {
        $this->grid = $grid->getRawGrid();
        $solved = $this->recursiveSolve();
        $grid->setRawGrid($this->grid);
        return $solved;
    }

    /**
     * Get empty cells in this grid
     *
     * @return array
     */
    protected function getEmptyCells(): array
    {
        $cells = [];

        foreach ($this->grid as $x => $col) {
            foreach ($col as $y => $value) {
                if ($value === null) {
                    $cells[] = [
                        $x,
                        $y
                    ];
                }
            }
        }

        return $cells;
    }


    /**
     * Attempt to solve a Sudoku puzzle
     *
     * @param int|null $lastX
     * @param int|null $lastY
     * @return bool
     * @throws \Exception
     */
    protected function recursiveSolve(?int $lastX = null, ?int $lastY = null): bool
    {
        // METHOD 1 - fill in any cells where there's only one option

        // Try searching around the last move to save time
        if ($lastX !== null && $lastY !== null) {
            $peers = $this->peers[$lastX][$lastY];
            $candidates = [];

            foreach ($peers as $peer) {
                // If it's not empty ignore it
                if (!empty($this->grid[$peer[0]][$peer[1]])) {
                    continue;
                }

                // Get moves for this square
                $potentialCandidates = $this->getCandidates($peer[0], $peer[1]);
                $candidateCount = count($potentialCandidates);

                // Oops, dead end situation - no point carrying on
                if ($candidateCount === 0) {
                    foreach ($candidates as $candidateSoFar) {
                        $this->grid[$candidateSoFar[0]][$candidateSoFar[1]] = null;
                    }
                    return false;
                }

                if ($candidateCount === 1) {
                    $this->grid[$peer[0]][$peer[1]] = $potentialCandidates[0];

                    $candidates[] = [
                        0 => $peer[0],
                        1 => $peer[1]
                    ];
                }
            }

            if (isset($candidates[0])) {
                // Recursively solve
                $solve = $this->recursiveSolve();

                // Even though this HAS to be right, a previous brute force move may have been wrong, so we may need to backtrack
                if (!$solve) {
                    // Undo all moves and pass up the chain we were wrong :(
                    foreach ($candidates as $candidate) {
                        $this->grid[$candidate[0]][$candidate[1]] = null;
                    }
                    // Backtrack
                    return false;
                }

                // We won!
                return true;
            }
        }


        // METHOD 2 - brute force by trying something that could fit
        $emptyCells = $this->getEmptyCells();
        $optimalCellMoveCount = 10;
        $x = null;
        $y = null;
        $optimalCandidates = null;

        // Find out what valid moves we have for each empty cell
        foreach ($emptyCells as $emptyCell) {
            $candidates = $this->getCandidates($emptyCell[0], $emptyCell[1]);
            $candidateCount = count($candidates);

            // If there's any cell we can't do anything with, we hit a dead end clearly
            if ($candidateCount === 0) {
                return false;
            }

            // We're looking for a move with fewest possible options - speeds things up
            if ($candidateCount < $optimalCellMoveCount) {
                $optimalCellMoveCount = $candidateCount;
                $optimalCandidates = $candidates;
                $x = $emptyCell[0];
                $y = $emptyCell[1];

                if ($candidateCount == 1) {
                    break;
                }
            }
        }

        // If there is a move to be made, let's make it!
        if (isset($optimalCandidates)) {;

            // Loop through possible values
            foreach ($optimalCandidates as $try) {
                // Set value
                $this->grid[$x][$y] = $try;

                // Recursively solve
                if ($this->recursiveSolve($x, $y)) {
                    // Yay, solved!
                    return true;
                }

                // Must have failed, backtrack for this cell and try next
                $this->grid[$x][$y] = null;
            }
            // Must be in a bad branch, need to backtrack :(
            return false;
        }

        // Winner winner chicken dinner - nothing left to complete so we must be finished!
        return true;
    }
}
